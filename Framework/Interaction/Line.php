<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Framework\Interaction;

use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use ClassyLlama\AvaTax\Helper\CustomsConfig;

class Line
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \ClassyLlama\AvaTax\Helper\TaxClass
     */
    protected $taxClassHelper;

    /**
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var CustomsConfig
     */
    protected $customsConfigHelper;

    /**
     * @var MetaData\MetaDataObject|null
     */
    protected $metaDataObject = null;

    /**
     * Description for shipping line
     */
    const SHIPPING_LINE_DESCRIPTION = 'Shipping costs';

    /**
     * Description for Gift Wrap order line
     */
    const GIFT_WRAP_ORDER_LINE_DESCRIPTION = 'Gift Wrap Order Amount';

    /**
     * Description for Gift Wrap item line
     */
    const GIFT_WRAP_ITEM_LINE_DESCRIPTION = 'Gift Wrap Items Amount';

    /**
     * Description for Gift Wrap card line
     */
    const GIFT_WRAP_CARD_LINE_DESCRIPTION = 'Gift Wrap Printed Card Amount';

    /**
     * Description for Adjustment refund line
     */
    const ADJUSTMENT_POSITIVE_LINE_DESCRIPTION = 'Adjustment refund';

    /**
     * Description for Adjustment fee line
     */
    const ADJUSTMENT_NEGATIVE_LINE_DESCRIPTION = 'Adjustment fee';

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.
     * Validation based on API documentation found here:
     * http://developer.avalara.com/wp-content/apireference/master/?php#line30
     *
     * @var array
     */
    public static $validFields = [
        'store_id' => ['type' => 'integer', 'use_in_cache_key' => false],
        'mage_sequence_no' => ['type' => 'string', 'length' => 50, 'required' => true],
        'number' => ['type' => 'integer', 'required' => false],
        'item_code' => ['type' => 'string', 'length' => 50],
        'tax_code' => ['type' => 'string', 'length' => 25],
        'description' => ['type' => 'string', 'length' => 255],
        'quantity' => ['type' => 'double'],
        'amount' => ['type' => 'double'], // Required, but $0 value is acceptable so removing required attribute.
        'discounted' => ['type' => 'boolean'],
        'tax_included' => ['type' => 'boolean'],
        'ref_1' => ['type' => 'string', 'length' => 250],
        'ref_2' => ['type' => 'string', 'length' => 250],
        'addresses' => [
            'type' => 'array',
            'subtype' => ['*' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject']],
        ],
        'hs_code' => ['type' => 'string', 'length' => 255],
        'unit_name' => ['type' => 'string', 'length' => 255],
        'unit_amount' => ['type' => 'double'],
        'preference_program' => ['type' => 'string', 'length' => 255],
    ];

    /**
     * Index that will be incremented for AvaTax line numbers
     *
     * @var int
     */
    protected $lineNumberIndex = 0;

    /**
     * Class constructor
     *
     * @param Config $config
     * @param \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper
     * @param \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param CustomsConfig $customsConfigHelper
     */
    public function __construct(
        Config $config,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper,
        \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger,
        MetaDataObjectFactory $metaDataObjectFactory,
        DataObjectFactory $dataObjectFactory,
        CustomsConfig $customsConfigHelper
    ) {
        $this->config = $config;
        $this->taxClassHelper = $taxClassHelper;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customsConfigHelper = $customsConfigHelper;
    }

    /**
     * Return an array with relevant data from an invoice item
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $item
     * @return \Magento\Framework\DataObject|bool
     */
    protected function convertInvoiceItemToData(\Magento\Sales\Api\Data\InvoiceItemInterface $item)
    {
        if (!$this->isProductCalculated($item->getOrderItem())) {
            return false;
        }

        // The AvaTax 15 API doesn't support the concept of line-based discounts, so subtract discount amount
        // from taxable amount
        $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        if ($item->getQty() == 0) {
            return false;
        }

        $storeId = $item->getInvoice()->getStoreId();
        $product = $item->getOrderItem()->getProduct();

        $itemData = $this->buildItemData($product, $storeId);

        if (!$itemData['itemCode']) {
            $itemData['itemCode'] = $item->getSku();
        }

        $data = [
            'store_id' => $storeId,
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemData['itemCode'],
            'tax_code' => $itemData['taxCode'],
            'description' => $item->getName(),
            'quantity' => $item->getQty(),
            'amount' => $amount,
            'tax_included' => false,
            'ref_1' => $itemData['productRef1'],
            'ref_2' => $itemData['productRef2']
        ];

        $extensionAttributes = $item->getExtensionAttributes();
        if ($this->customsConfigHelper->enabled() && $extensionAttributes) {
            if ($extensionAttributes->getHsCode() !== null) {
                $data['hs_code'] = $extensionAttributes->getHsCode();
            }
            if ($extensionAttributes->getUnitName() !== null) {
                $data['unit_name'] = $extensionAttributes->getUnitName();
            }
            if ($extensionAttributes->getUnitAmount() !== null) {
                $data['unit_amount'] = $extensionAttributes->getUnitAmount();
            }
            if ($extensionAttributes->getPrefProgramIndicator() !== null) {
                $data['preference_program'] = $extensionAttributes->getPrefProgramIndicator();
            }
        }

        /** @var \Magento\Framework\DataObject $line */
        $line = $this->dataObjectFactory->create(['data' => $data]);

        return $line;
    }

    /**
     * Return an array with relevant data from an credit memo item
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $item
     * @return \Magento\Framework\DataObject|bool
     */
    protected function convertCreditMemoItemToData(\Magento\Sales\Api\Data\CreditmemoItemInterface $item)
    {
        if (!$this->isProductCalculated($item->getOrderItem())) {
            return false;
        }

        // The AvaTax 15 API doesn't support the concept of line-based discounts, so subtract discount amount
        // from taxable amount
        $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        // Credit memo amounts need to be sent to AvaTax as negative numbers
        $amount *= -1;

        if ($item->getQty() == 0) {
            return false;
        }

        $storeId = $item->getCreditmemo()->getStoreId();
        $product = $item->getOrderItem()->getProduct();

        $itemData = $this->buildItemData($product, $storeId);

        if (!$itemData['itemCode']) {
            $itemData['itemCode'] = $item->getSku();
        }

        $data = [
            'store_id' => $storeId,
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemData['itemCode'],
            'tax_code' => $itemData['taxCode'],
            'description' => $item->getName(),
            'quantity' => $item->getQty(),
            'amount' => $amount,
            'tax_included' => false,
            'ref_1' => $itemData['productRef1'],
            'ref_2' => $itemData['productRef2']
        ];

        $extensionAttributes = $item->getExtensionAttributes();
        if ($this->customsConfigHelper->enabled() && $extensionAttributes) {
            if ($extensionAttributes->getHsCode() !== null) {
                $data['hs_code'] = $extensionAttributes->getHsCode();
            }
            if ($extensionAttributes->getUnitName() !== null) {
                $data['unit_name'] = $extensionAttributes->getUnitName();
            }
            if ($extensionAttributes->getUnitAmount() !== null) {
                $data['unit_amount'] = $extensionAttributes->getUnitAmount();
            }
            if ($extensionAttributes->getPrefProgramIndicator() !== null) {
                $data['preference_program'] = $extensionAttributes->getPrefProgramIndicator();
            }
        }

        /** @var \Magento\Framework\DataObject $line */
        $line = $this->dataObjectFactory->create(['data' => $data]);

        return $line;
    }

    /**
     * Convert \Magento\Tax\Model\Sales\Quote\ItemDetails to an array to be used for building a line object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item
     * @return \Magento\Framework\DataObject
     */
    protected function convertTaxQuoteDetailsItemToData(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $item)
    {
        $extensionAttributes = $item->getExtensionAttributes();
        if ($extensionAttributes) {
            $quantity = $extensionAttributes->getTotalQuantity() !== null
                ? $extensionAttributes->getTotalQuantity()
                : $item->getQuantity();
        } else {
            $quantity = $item->getQuantity();
        }

        $itemCode = $extensionAttributes ? $extensionAttributes->getAvataxItemCode() : '';
        $description = $extensionAttributes ? $extensionAttributes->getAvataxDescription() : '';
        $taxCode = $extensionAttributes ? $extensionAttributes->getAvataxTaxCode() : null;

        // Calculate tax with or without discount based on config setting
        if ($this->config->getCalculateTaxBeforeDiscount($item->getStoreId())) {
            $amount = $item->getUnitPrice() * $quantity;
        } else {
            $amount = ($item->getUnitPrice() * $quantity) - $item->getDiscountAmount();
        }

        $ref1 = $extensionAttributes ? $extensionAttributes->getAvataxRef1() : null;
        $ref2 = $extensionAttributes ? $extensionAttributes->getAvataxRef2() : null;

        $data = [
            'mage_sequence_no' => $item->getCode(),
            'item_code' => $itemCode,
            'tax_code' => $taxCode,
            'description' => $description,
            'quantity' => $item->getQuantity(),
            'amount' => $amount,
            'tax_included' => false,
            'ref_1' => $ref1,
            'ref_2' => $ref2,
        ];

        if ($this->customsConfigHelper->enabled() && $extensionAttributes) {
            if ($extensionAttributes->getHsCode() !== null) {
                $data['hs_code'] = $extensionAttributes->getHsCode();
            }
            if ($extensionAttributes->getUnitName() !== null) {
                $data['unit_name'] = $extensionAttributes->getUnitName();
            }
            if ($extensionAttributes->getUnitAmount() !== null) {
                $data['unit_amount'] = $extensionAttributes->getUnitAmount();
            }
            if ($extensionAttributes->getPrefProgramIndicator() !== null) {
                $data['preference_program'] = $extensionAttributes->getPrefProgramIndicator();
            }
        }

        /** @var \Magento\Framework\DataObject $line */
        $line = $this->dataObjectFactory->create(['data' => $data]);

        return $line;
    }

    /**
     * Get tax line object
     *
     * @param $data
     * @return \Magento\Framework\DataObject|null|bool
     */
    public function getLine($data)
    {
        /** @var \Magento\Framework\DataObject $line */
        $line = false;
        switch (true) {
            case ($data instanceof \Magento\Tax\Api\Data\QuoteDetailsItemInterface):
                $line = $this->convertTaxQuoteDetailsItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                $line = $this->convertInvoiceItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                $line = $this->convertCreditMemoItemToData($data);
                break;
            case (!is_array($data)):
                return false;
                break;
        }

        if (!$line) {
            return null;
        }

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @param $credit
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getShippingLine($data, $credit)
    {
        $shippingAmount = $data->getBaseShippingAmount();

        // If shipping rate doesn't have cost associated with it, do nothing
        if ($shippingAmount <= 0) {
            return false;
        }

        // Check the order to see if a shipping discount amount exists
        // and the shipping amount on the invoice|creditmemo matches the shipping amount on the order
        // then subtract the discount amount from the shipping amount and if 0 return false
        $shippingDiscountAmount = $data->getOrder()->getShippingDiscountAmount();
        $orderShippingAmount = $data->getOrder()->getShippingAmount();
        if (
            $shippingDiscountAmount > 0
            && $shippingAmount == $orderShippingAmount
            && $shippingAmount - $shippingDiscountAmount >= 0
        ) {
            $shippingAmount = $shippingAmount - $shippingDiscountAmount;
        }


        if ($credit) {
            $shippingAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShipping($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForShipping(),
            'description' => self::SHIPPING_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $shippingAmount,
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @param $credit
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getGiftWrapOrderLine($data, $credit)
    {
        $giftWrapOrderAmount = $data->getGwBasePrice();

        if ($giftWrapOrderAmount <= 0) {
            return false;
        }

        if ($credit) {
            $giftWrapOrderAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuGiftWrapOrder($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_ORDER_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $giftWrapOrderAmount,
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);


        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @param $credit
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getGiftWrapItemsLine($data, $credit) {
        $giftWrapItemsPrice = $data->getGwItemsBasePrice();

        if ($giftWrapItemsPrice <= 0) {
            return false;
        }

        $giftWrapItemAmount = $giftWrapItemsPrice;

        if ($credit) {
            $giftWrapItemAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShippingGiftWrapItem($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_ITEM_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $giftWrapItemAmount,
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @param $credit
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getGiftWrapCardLine($data, $credit) {
        $giftWrapCardAmount = $data->getGwCardBasePrice();

        if ($giftWrapCardAmount <= 0) {
            return false;
        }

        if ($credit) {
            $giftWrapCardAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShippingGiftWrapCard($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_CARD_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $giftWrapCardAmount,
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getPositiveAdjustmentLine($data) {
        $amount = $data->getBaseAdjustmentPositive();

        if ($amount == 0) {
            return false;
        }

        // Credit memo amounts need to be sent to AvaTax as negative numbers
        $amount *= -1;

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuAdjustmentPositive($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            // Intentionally excluding TaxCode key
            'description' => self::ADJUSTMENT_POSITIVE_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $amount,
            // Since taxes will already be included in this amount, set this flag to true
            'tax_included' => true
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($data, true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns a line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \Magento\Framework\DataObject|bool
     * @throws ValidationException
     */
    public function getNegativeAdjustmentLine($data) {
        $amount = $data->getBaseAdjustmentNegative();

        if ($amount == 0) {
            return false;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuAdjustmentNegative($storeId);
        $data = [
            'mage_sequence_no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            // Intentionally excluding TaxCode key
            'description' => self::ADJUSTMENT_NEGATIVE_LINE_DESCRIPTION,
            'quantity' => 1,
            'amount' => $amount,
            // Since taxes will already be included in this amount, set this flag to true
            'tax_included' => true
        ];
        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }

    /**
     * Get line number for AvaTax "No" field
     *
     * @return int
     */
    protected function getLineNumber()
    {
        return ++$this->lineNumberIndex;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     */
    protected function isProductCalculated(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        // @see \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItems
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $product
     * @param integer $storeId
     * @return array
     */
    protected function buildItemData($product, $storeId)
    {
        if ($product) {
            $data =
            [
                'itemCode' => $this->taxClassHelper->getItemCodeOverride($product),
                'taxCode' => $this->taxClassHelper->getAvataxTaxCodeForProduct($product, $storeId),
                'productRef1' => $this->taxClassHelper->getRef1ForProduct($product),
                'productRef2' => $this->taxClassHelper->getRef2ForProduct($product)
            ];
        } else {
            // Using null values for these parameters since the product can no longer be found; they're null by default
            // and only have values if explicitly defined in the configuration. Using nulls won't prevent submission to
            // Avalara and will only raise an issue if the product had these values before being deleted.
            $data =
                [
                    'itemCode' => null,
                    'taxCode' => null,
                    'productRef1' => null,
                    'productRef2' => null
                ];
        }
        return $data;
    }
}
