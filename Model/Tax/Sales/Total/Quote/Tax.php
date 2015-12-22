<?php
/**
 * Tax.php
 *
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Erik Hansen <erik@classyllama.com>
 * @copyright   Copyright (c) 2015 Erik Hansen & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Model\Calculation;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
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
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->taxCalculation = $taxCalculation;
        $this->config = $config;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionFactory = $extensionFactory;
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
        $storeId = $quote->getStoreId();
        // This will allow a merchant to configure default tax settings for their site using Magento's core tax
        // calculation and AvaTax's calculation will only kick in during cart/checkout. This is useful for countries
        // where merchants are required to display prices including tax (such as some countries that charge VAT tax).
        if (!$this->config->isModuleEnabled($storeId)) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        // If postcode is not present, then collect totals is being run from a context where customer has not submitted
        // their address, such as on the product listing, product detail, or cart page. Once the user enters their
        // postcode in the "Estimate Shipping & Tax" form on the cart page, or submits their shipping address in the
        // checkout, then a postcode will be present.
        $postcode = $shippingAssignment->getShipping()->getAddress()->getPostcode();
        if (!$postcode) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $taxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, false);
        $baseTaxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, true);

        // Get array of tax details
        $taxDetailsList = $this->interactionGetTax->getTaxDetailsForQuote($quote, $taxQuoteDetails, $baseTaxQuoteDetails, $shippingAssignment);

        if (!$taxDetailsList) {
            switch ($this->config->getErrorAction($quote->getStoreId())) {
                case Config::ERROR_ACTION_DISABLE_CHECKOUT:
                    $errorMessage = $this->config->getErrorActionDisableCheckoutMessage($quote->getStoreId());
                    // TODO: This exception gets caught by the last try/catch block in \Magento\Checkout\Model\ShippingInformationManagement::saveAddressInformation, so getting our custom exception message to display to user will take a different approach
                    /**
                     * Using this exception type will cause Magento to display this error message to the user when
                     * request is made from the web API
                     * @see \Magento\Framework\Webapi\ErrorProcessor::maskException
                     */
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                    break;
                case Config::ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX:
                default:
                    break;
            }
        } else {
            $taxDetails = $taxDetailsList[InteractionGet::KEY_TAX_DETAILS];
            $baseTaxDetails = $taxDetailsList[InteractionGet::KEY_BASE_TAX_DETAILS];
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
     * @return \Magento\Tax\Api\Data\QuoteDetailsInterface
     */
    protected function getTaxQuoteDetails($shippingAssignment, $total, $useBaseCurrency)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $this->addInfoToQuoteDetailsItemForShipping($shippingDataObject);
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
        if (!$this->config->isModuleEnabled($storeId)) {
            return $quoteDetailsItem;
        }

        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttribute */
        $extensionAttribute = $quoteDetailsItem->getExtensionAttributes()
            ? $quoteDetailsItem->getExtensionAttributes()
            : $this->extensionFactory->create();

        $extensionAttribute->setAvataxItemCode($item->getSku());
        $extensionAttribute->setAvataxDescription($item->getName());
        // TODO: Implement logic for Ref1/Ref2
        //$extensionAttribute->setAvataxRef1();
        //$extensionAttribute->setAvataxRef2();
        $quoteDetailsItem->setExtensionAttributes($extensionAttribute);

        return $quoteDetailsItem;
    }

    /**
     * Add extension attribute fields to the \Magento\Tax\Model\Sales\Quote\ItemDetails object for the shipping record
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject
     * @return $this
     */
    protected function addInfoToQuoteDetailsItemForShipping(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject)
    {
        $itemCode = \ClassyLlama\AvaTax\Framework\Interaction\Line::SHIPPING_LINE_TAX_CODE;
        $itemDescription = \ClassyLlama\AvaTax\Framework\Interaction\Line::SHIPPING_LINE_DESCRIPTION;

        $this->addExtensionAttributesToTaxQuoteDetailsItem(
            $shippingDataObject,
            $itemCode,
            $itemDescription
        );
        return $this;
    }

    /**
     * Add AvaTax specific extension attribute fields to a \Magento\Tax\Model\Sales\Quote\ItemDetails object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $quoteDetailsItem
     * @param $avaTaxItemCode
     * @param $avaTaxDescription
     * @return $this
     */
    protected function addExtensionAttributesToTaxQuoteDetailsItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $quoteDetailsItem,
        $avaTaxItemCode,
        $avaTaxDescription
    ) {
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttribute */
        $extensionAttribute = $quoteDetailsItem->getExtensionAttributes()
            ? $quoteDetailsItem->getExtensionAttributes()
            : $this->extensionFactory->create();

        $extensionAttribute->setAvataxItemCode($avaTaxItemCode);
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
        if (!$this->config->isModuleEnabled($storeId)) {
            return $itemDataObjects;
        }

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case Giftwrapping::ITEM_TYPE:
                    $itemCode = $this->config->getSkuShippingGiftWrapItem($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
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
        if (!$this->config->isModuleEnabled($storeId)) {
            return $itemDataObjects;
        }

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case Giftwrapping::QUOTE_TYPE:
                    $itemCode = $this->config->getSkuGiftWrapOrder($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
                case Giftwrapping::PRINTED_CARD_TYPE:
                    $itemCode = $this->config->getSkuShippingGiftWrapCard($storeId);
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
            }
        }

        return $itemDataObjects;
    }
}
