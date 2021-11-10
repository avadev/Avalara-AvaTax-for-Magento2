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

namespace ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation as TaxCalculation;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax\Customs as CustomsTax;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    /**
     * Gift wrapping tax class
     *
     * Copied from \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping it is an Enterprise-only module
     */
    const ITEM_TYPE = 'item_gw';
    const QUOTE_TYPE = 'quote_gw';
    const PRINTED_CARD_TYPE = 'printed_card_gw';
    /**
     * @var InteractionGet
     */
    protected $interactionGetTax = null;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \ClassyLlama\AvaTax\Helper\TaxClass
     */
    protected $taxClassHelper;

    /**
     * @var CustomsTax
     */
    protected $customsTax;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * Registry key to track whether AvaTax GetTaxRequest was successful
     */
    const AVATAX_GET_TAX_REQUEST_ERROR = 'avatax_get_tax_request_error';

    const AVATAX_GET_TAX_REQUEST_ERROR_IDS = 'avatax_get_tax_request_error_ids';

    const MINIMUM_POST_CODE_LENGTH = 3;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param InteractionGet $interactionGetTax
     * @param TaxCalculation $taxCalculation
     * @param Config $config
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper
     * @param CustomsTax $customsTax
     * @param \Magento\Framework\Session\Generic $session
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        InteractionGet $interactionGetTax,
        TaxCalculation $taxCalculation,
        Config $config,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper,
        CustomsTax $customsTax,
        \Magento\Framework\Session\Generic $session
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->taxCalculation = $taxCalculation;
        $this->config = $config;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionFactory = $extensionFactory;
        $this->messageManager = $messageManager;
        $this->coreRegistry = $coreRegistry;
        $this->taxClassHelper = $taxClassHelper;
        $this->customsTax = $customsTax;
        $this->session = $session;
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory,
            $taxData
        );
    }

    /**
     * Collect tax totals for quote address
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @throws RemoteServiceUnavailableException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Address\Total $total
    ) {
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $address = $shippingAssignment->getShipping()->getAddress();

        // Reset any messaging unless we actually run our collector, useful when estimating tax in non-enabled countries
        $total->setAvataxMessages(null);

        $storeId = $quote->getStoreId();
        // This will allow a merchant to configure default tax settings for their site using Magento's core tax
        // calculation and AvaTax's calculation will only kick in during cart/checkout. This is useful for countries
        // where merchants are required to display prices including tax (such as some countries that charge VAT tax).
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            || !$this->config->isAddressTaxable($address, $storeId)
        ) {
            return parent::collect($quote, $shippingAssignment, $total);
        }


        $postcode = $address->getPostcode();
        // If postcode is not present, then collect totals is being run from a context where customer has not submitted
        // their address, such as on the product listing, product detail, or cart page. Once the user enters their
        // postcode in the "Estimate Shipping & Tax" form on the cart page, or submits their shipping address in the
        // checkout, then a postcode will be present; but only send request if the postcode is at least 3 characters.
        if (!$postcode || \strlen($postcode) < static::MINIMUM_POST_CODE_LENGTH) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $this->customsTax->assignCrossBorderDetails($shippingAssignment);
        $taxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, $storeId, false);
        $baseTaxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, $storeId, true);

        // Get array of tax details
        try {
            list($taxDetails, $baseTaxDetails, $avaTaxMessages) = $this->interactionGetTax->getTaxDetailsForQuote(
                $quote,
                $taxQuoteDetails,
                $baseTaxQuoteDetails,
                $shippingAssignment
            );
        } catch (\Exception $e) {
            switch ($this->config->getErrorAction($quote->getStoreId())) {
                case Config::ERROR_ACTION_DISABLE_CHECKOUT:
                    $this->coreRegistry->register(self::AVATAX_GET_TAX_REQUEST_ERROR, true, true);
                    $ids = (!empty($this->session->getAvataxGetTaxRequestErrorIds())) ? $this->session->getAvataxGetTaxRequestErrorIds() : [];
                    if (!is_null($address->getId()) && !in_array($address->getId(), $ids)) {
                        array_push($ids, $address->getId());
                    }
                    $this->session->setAvataxGetTaxRequestErrorIds($ids);
                    return parent::collect($quote, $shippingAssignment, $total);
                    break;
                case Config::ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX:
                default:
                    /**
                     * Note: while this should return Magento's tax calculation, the tax calculation may be slightly
                     * off, as these two collect methods will not have run:
                     * @see \Magento\Tax\Model\Sales\Total\Quote\Shipping::collect()
                     * @see \Magento\Tax\Model\Sales\Total\Quote\Subtotal::collect()
                     */
                    return parent::collect($quote, $shippingAssignment, $total);
                    break;
            }
        }

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($shippingAssignment, $total, $shippingTaxDetails, $baseShippingTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($total, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }

        $total->setAvataxMessages(json_encode($avaTaxMessages));

        return $this;
    }

    /**
     * Generate \Magento\Tax\Model\Sales\Quote\QuoteDetails object based on shipping assignment
     *
     * Base closely on this method, with the exception of the tax calculation call to calculate taxes:
     * @see \Magento\Tax\Model\Sales\Total\Quote\Tax::getQuoteTaxDetails()
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @param bool $useBaseCurrency
     * @param string $storeId
     * @return \Magento\Tax\Api\Data\QuoteDetailsInterface
     */
    protected function getTaxQuoteDetails($shippingAssignment, $total, $storeId, $useBaseCurrency)
    {
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $address = $shippingAssignment->getShipping()->getAddress();
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $this->addInfoToQuoteDetailsItemForShipping($shippingDataObject, $storeId);
            $itemDataObjects[] = $shippingDataObject;
        }

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemDataObjectFactory,
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);

        return $quoteDetails;
    }

    /**
     * Map an item to item data object. Add AvaTax details to extension objects.
     *
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Item\AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function mapItem(
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $quoteDetailsItem = parent::mapItem($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);

        $storeId = $item->getStore()->getId();
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            // While it would be idea to check $this->config->isAddressTaxable, we don't have the address in this
            // scope, so it's not possible to do so. However since all this method is doing is adding extension
            // attribute data, it's ok if this method runs even if tax is not being calculated for this item
        ) {
            return $quoteDetailsItem;
        }

        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttribute */
        $extensionAttribute = $quoteDetailsItem->getExtensionAttributes()
            ? $quoteDetailsItem->getExtensionAttributes()
            : $this->extensionFactory->create();

        $product = $item->getProduct();
        $taxCode = $this->taxClassHelper->getAvataxTaxCodeForProduct($product, $storeId);
        $itemCode = $this->taxClassHelper->getItemCodeOverride($product);
        if (!$itemCode) {
            $itemCode = $item->getSku();
        }
        $extensionAttribute->setAvataxItemCode($itemCode);
        $extensionAttribute->setAvataxTaxCode($taxCode);
        $extensionAttribute->setAvataxDescription($item->getName());
        $extensionAttribute->setAvataxRef1($this->taxClassHelper->getRef1ForProduct($product));
        $extensionAttribute->setAvataxRef2($this->taxClassHelper->getRef2ForProduct($product));

        // Cross-border details. These should not exist on the quote item if Customs is not enabled.
        $quoteItemExtAttribute = $item->getExtensionAttributes();
        if ($quoteItemExtAttribute) {
            $extensionAttribute->setHsCode($quoteItemExtAttribute->getHsCode());
            $extensionAttribute->setUnitName($quoteItemExtAttribute->getUnitName());
            $extensionAttribute->setUnitAmount($quoteItemExtAttribute->getUnitAmount());
            $extensionAttribute->setPrefProgramIndicator($quoteItemExtAttribute->getPrefProgramIndicator());
        }

        $quoteDetailsItem->setExtensionAttributes($extensionAttribute);

        return $quoteDetailsItem;
    }

    /**
     * Add extension attribute fields to the \Magento\Tax\Model\Sales\Quote\ItemDetails object for the shipping record
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject
     * @param $storeId
     * @return $this
     */
    protected function addInfoToQuoteDetailsItemForShipping(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject,
        $storeId
    ) {
        $itemCode = $this->config->getSkuShipping($storeId);
        $itemDescription = \ClassyLlama\AvaTax\Framework\Interaction\Line::SHIPPING_LINE_DESCRIPTION;
        $taxCode = $this->taxClassHelper->getAvataxTaxCodeForShipping();

        $this->addExtensionAttributesToTaxQuoteDetailsItem(
            $shippingDataObject,
            $itemCode,
            $taxCode,
            $itemDescription
        );
        return $this;
    }

    /**
     * Add AvaTax specific extension attribute fields to a \Magento\Tax\Model\Sales\Quote\ItemDetails object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $quoteDetailsItem
     * @param $avaTaxItemCode
     * @param $avaTaxTaxCode
     * @param $avaTaxDescription
     * @return $this
     */
    protected function addExtensionAttributesToTaxQuoteDetailsItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $quoteDetailsItem,
        $avaTaxItemCode,
        $avaTaxTaxCode,
        $avaTaxDescription
    ) {
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttribute */
        $extensionAttribute = $quoteDetailsItem->getExtensionAttributes()
            ? $quoteDetailsItem->getExtensionAttributes()
            : $this->extensionFactory->create();

        $extensionAttribute->setAvataxItemCode($avaTaxItemCode);
        $extensionAttribute->setAvataxTaxCode($avaTaxTaxCode);
        $extensionAttribute->setAvataxDescription($avaTaxDescription);
        $quoteDetailsItem->setExtensionAttributes($extensionAttribute);
        return $this;
    }

    /**
     * Map item extra taxables
     *
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Item\AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItemExtraTaxables(
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $itemDataObjects = parent::mapItemExtraTaxables(
            $itemDataObjectFactory,
            $item,
            $priceIncludesTax,
            $useBaseCurrency
        );

        $storeId = $item->getStore()->getId();
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            // While it would be idea to check $this->config->isAddressTaxable, we don't have the address in this
            // scope, so it's not possible to do so. However since all this method is doing is adding extension
            // attribute data, it's ok if this method runs even if tax is not being calculated for this item
        ) {
            return $itemDataObjects;
        }

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case self::ITEM_TYPE:
                    $itemCode = $this->config->getSkuShippingGiftWrapItem($storeId);
                    $taxCode = $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        $taxCode,
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ITEM_LINE_DESCRIPTION
                    );
                    break;
            }
        }

        return $itemDataObjects;
    }

    /**
     * Map extra taxables associated with quote. Add AvaTax details to extension objects.
     *
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapQuoteExtraTaxables(
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Address $address,
        $useBaseCurrency
    ) {
        $itemDataObjects = parent::mapQuoteExtraTaxables(
            $itemDataObjectFactory,
            $address,
            $useBaseCurrency
        );

        $storeId = $address->getQuote()->getStore()->getId();
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            || !$this->config->isAddressTaxable($address, $storeId)
        ) {
            return $itemDataObjects;
        }

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case self::QUOTE_TYPE:
                    $itemCode = $this->config->getSkuGiftWrapOrder($storeId);
                    $taxCode = $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        $taxCode,
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
                case self::PRINTED_CARD_TYPE:
                    $itemCode = $this->config->getSkuShippingGiftWrapCard($storeId);
                    $taxCode = $this->taxClassHelper->getAvataxTaxCodeForGiftOptions($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        $taxCode,
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
            }
        }

        return $itemDataObjects;
    }
}
