<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\LineFactory;
use ClassyLlama\AvaTax\Helper\Validation;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;

class Line
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var \ClassyLlama\AvaTax\Helper\TaxClass
     */
    protected $taxClassHelper;

    /**
     * @var Validation
     */
    protected $validation = null;

    /**
     * @var LineFactory
     */
    protected $lineFactory = null;

    /**
     * @var ResourceProduct
     */
    protected $resourceProduct = null;

    /**
     * @var array
     */
    protected $productSkus = [];

    /**
     * Description for shipping line
     */
    const SHIPPING_LINE_DESCRIPTION = 'Shipping costs';

    /**
     * An arbitrary ID used to track tax for shipping
     */
    const SHIPPING_LINE_NO = 'shipping';

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
    protected $validDataFields = [
        'store_id' => ['type' => 'integer'],
        'no' => ['type' => 'string', 'length' => 50, 'required' => true],
        'origin_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'destination_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'item_code' => ['type' => 'string', 'length' => 50],
        'tax_code' => ['type' => 'string', 'length' => 25],
        'customer_usage_type' => ['type' => 'string', 'length' => 25],
        'exemption_no' => ['type' => 'string', 'length' => 25],
        'description' => ['type' => 'string', 'length' => 255],
        'qty' => ['type' => 'float'],
        'amount' => ['type' => 'float'], // Required but $0 value is acceptable so removing required attribute.
        'discounted' => ['type' => 'boolean'],
        'tax_included' => ['type' => 'boolean'],
        'ref1' => ['type' => 'string', 'length' => 250],
        'ref2' => ['type' => 'string', 'length' => 250],
        'tax_override' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
    ];

    /**
     * Index that will be incremented for \AvaTax\Line numbers
     *
     * @var int
     */
    protected $lineNumberIndex = 0;

    /**
     * Class constructor
     *
     * @param Config $config
     * @param \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper
     * @param Validation $validation
     * @param LineFactory $lineFactory
     * @param ResourceProduct $resourceProduct
     */
    public function __construct(
        Config $config,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper,
        Validation $validation,
        LineFactory $lineFactory,
        ResourceProduct $resourceProduct
    ) {
        $this->config = $config;
        $this->taxClassHelper = $taxClassHelper;
        $this->validation = $validation;
        $this->lineFactory = $lineFactory;
        $this->resourceProduct = $resourceProduct;
    }

    /**
     * Return an array with relevant data from an invoice item
     *
     * All TODOs in the doc block and the method body apply to all 4 conversion methods
     * TODO: tax_code can either be custom or system.  Custom tax codes can be configured in the AvaTax admin to set up specific tax reductions or exemptions for certain products.  In AvaTax Pro, there are many system tax codes that can be passed depending on the type of item that is being sold.  This really belongs on the product level although we could also put it on the Tax Class level as well.  The M1 module just uses the same value for this as for customer_usage_type which is confusing and incorrect for cases where you may want to pass both on the same item.  We should at least implement this as a text field on either the product, the tax class, or both.  We could possibly implement this as a more configurable option but that really seems like a phase 2 or phase 3 feature. More information: https://help.avalara.com/000_AvaTax_Calc/000AvaTaxCalc_User_Guide/051_Select_AvaTax_System_Tax_Codes and http://developer.avalara.com/api-docs/designing-your-integration/gettax.
     * TODO: Wishlist Product Attribute for tax_code
     * TODO: Fields to figure out: tax_override
     * TODO: Use Tax Class to get customer_usage_type, once this functionality is implemented
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $item
     * @return array|bool
     */
    protected function convertInvoiceItemToData(\Magento\Sales\Api\Data\InvoiceItemInterface $item)
    {
        if (!$this->isProductCalculated($item->getOrderItem())) {
            return false;
        }

        // The AvaTax 15 API doesn't support the concept of line-based discounts, so subtract discount amount
        // from taxable amount
        $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        if ($item->getQty() == 0 || $amount == 0) {
            return false;
        }

        return [
            'store_id' => $item->getStoreId(),
            'no' => $this->getLineNumber(),
            'item_code' => $item->getSku(),
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForProduct($item->getOrderItem()->getProduct()),
//            'customer_usage_type' => null,
            'description' => $item->getName(),
            'qty' => $item->getQty(),
            'amount' => $amount,
            'discounted' => (bool)($item->getBaseDiscountAmount() > 0),
            'tax_included' => false,
            'ref1' => $this->config->getRef1($item->getStoreId()), // TODO: Switch to getting values from buy request and put data on buy request
            'ref2' => $this->config->getRef2($item->getStoreId()),
//            'tax_override' => null,
        ];
    }

    /**
     * Return an array with relevant data from an credit memo item
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $item
     * @return array|bool
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

        if ($item->getQty() == 0 || $amount == 0) {
            return false;
        }

        return [
            'store_id' => $item->getStoreId(),
            'no' => $this->getLineNumber(),
            'item_code' => $item->getSku(),
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForProduct($item->getOrderItem()->getProduct()),
//            'customer_usage_type' => null,
            'description' => $item->getName(),
            'qty' => $item->getQty(),
            'amount' => $amount,
            'discounted' => (bool)($item->getBaseDiscountAmount() > 0),
            'tax_included' => false,
            'ref1' => $this->config->getRef1($item->getStoreId()), // TODO: Switch to getting values from buy request and put data on buy request
            'ref2' => $this->config->getRef2($item->getStoreId()),
//            'tax_override' => null,
        ];
    }

    /**
     * Convert \Magento\Tax\Model\Sales\Quote\ItemDetails to an array to be used for building an \AvaTax\Line object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item
     * @return array
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

        // The AvaTax 15 API doesn't support the concept of line-based discounts, so subtract discount amount
        // from taxable amount
        $amount = ($item->getUnitPrice() * $quantity) - $item->getDiscountAmount();

        $ref1 = $extensionAttributes ? $extensionAttributes->getAvataxRef1() : null;
        $ref2 = $extensionAttributes ? $extensionAttributes->getAvataxRef2() : null;

        return [
//            'store_id' => $item->getStoreId(),
            'no' => $item->getCode(),
            'item_code' => $itemCode,
            'tax_code' => $taxCode,
//            'customer_usage_type' => null,
            'description' => $description,
            'qty' => $item->getQuantity(),
            'amount' => $amount,
            'discounted' => (bool)($item->getDiscountAmount() > 0),
            'tax_included' => false,
            'ref1' => $ref1,
            'ref2' => $ref2,
        ];
    }

    /**
     *
     * TODO: Figure out if we need to account for Streamlined Sales Tax requirements for description
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return \AvaTax\Line|null|bool
     */
    public function getLine($data)
    {
        switch (true) {
            case ($data instanceof \Magento\Tax\Api\Data\QuoteDetailsItemInterface):
                $data = $this->convertTaxQuoteDetailsItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                $data = $this->convertInvoiceItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                $data = $this->convertCreditMemoItemToData($data);
                break;
            case (!is_array($data)):
                return false;
                break;
        }

        if (!$data) {
            return null;
        }

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
     */
    public function getShippingLine($data, $credit)
    {
        $shippingAmount = $data->getBaseShippingAmount();

        // If shipping rate doesn't have cost associated with it, do nothing
        if ($shippingAmount <= 0) {
            return false;
        }

        if ($credit) {
            $shippingAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShipping($storeId);
        $data = [
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForShipping(),
            'description' => self::SHIPPING_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $shippingAmount,
            'discounted' => false,
        ];

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
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
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_ORDER_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $giftWrapOrderAmount,
            'discounted' => false,
        ];


        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
     */
    public function getGiftWrapItemsLine($data, $credit) {
        $giftWrapItemsPrice = $data->getGwItemsBasePrice();

        if ($giftWrapItemsPrice <= 0) {
            return false;
        }
//        $qty = $data->getTotalQty();

//        $giftWrapItemAmount = $giftWrapItemsPrice * $qty;
        $giftWrapItemAmount = $giftWrapItemsPrice;

        if ($credit) {
            $giftWrapItemAmount *= -1;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShippingGiftWrapItem($storeId);
        $data = [
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_ITEM_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $giftWrapItemAmount,
            'discounted' => false,
        ];

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
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
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            'tax_code' => $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId),
            'description' => self::GIFT_WRAP_CARD_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $giftWrapCardAmount,
            'discounted' => false,
        ];

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
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
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            // Intentionally excluding tax_code key
            'description' => self::ADJUSTMENT_POSITIVE_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $amount,
            'discounted' => false,
            // Since taxes will already be included in this amount, set this flag to true
            'tax_included' => true
        ];

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $data
     * @return \AvaTax\Line|bool
     */
    public function getNegativeAdjustmentLine($data) {
        $amount = $data->getBaseAdjustmentNegative();

        if ($amount == 0) {
            return false;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuAdjustmentNegative($storeId);
        $data = [
            'no' => $this->getLineNumber(),
            'item_code' => $itemCode,
            // Intentionally excluding tax_code key
            'description' => self::ADJUSTMENT_NEGATIVE_LINE_DESCRIPTION,
            'qty' => 1,
            'amount' => $amount,
            'discounted' => false,
            // Since taxes will already be included in this amount, set this flag to true
            'tax_included' => true
        ];

        $data = $this->validation->validateData($data, $this->validDataFields);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        $this->populateLine($data, $line);
        return $line;
    }
    /**
     * @param array $data
     * @param \AvaTax\Line $line
     */
    protected function populateLine(array $data, \AvaTax\Line $line)
    {
        if (isset($data['no'])) {
            $line->setNo($data['no']);
        }
        if (isset($data['origin_address'])) {
            $line->setOriginAddress($data['origin_address']);
        }
        if (isset($data['destination_address'])) {
            $line->setDestinationAddress($data['destination_address']);
        }
        if (isset($data['item_code'])) {
            $line->setItemCode($data['item_code']);
        }
        if (isset($data['tax_code'])) {
            $line->setTaxCode($data['tax_code']);
        }
        if (isset($data['customer_usage_type'])) {
            $line->setCustomerUsageType($data['customer_usage_type']);
        }
        if (isset($data['exemption_no'])) {
            $line->setExemptionNo($data['exemption_no']);
        }
        if (isset($data['description'])) {
            $line->setDescription($data['description']);
        }
        if (isset($data['qty'])) {
            $line->setQty($data['qty']);
        }
        if (isset($data['amount'])) {
            $line->setAmount($data['amount']);
        }
        if (isset($data['discounted'])) {
            $line->setDiscounted($data['discounted']);
        }
        if (isset($data['tax_included'])) {
            $line->setTaxIncluded($data['tax_included']);
        }
        if (isset($data['ref1'])) {
            $line->setRef1($data['ref1']);
        }
        if (isset($data['ref2'])) {
            $line->setRef2($data['ref2']);
        }
        if (isset($data['tax_override'])) {
            $line->setTaxOverride($data['tax_override']);
        }
    }

    /**
     * Get line number for \AvaTax\Line "no" field
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
}
