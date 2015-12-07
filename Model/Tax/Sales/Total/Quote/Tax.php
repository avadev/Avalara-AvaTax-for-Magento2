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
use ClassyLlama\AvaTax\Framework\Interaction\TaxDetailsItem;
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
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    /**
     * @var InteractionGet
     */
    protected $interactionGetTax = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var TaxDetailsItem
     */
    protected $taxDetailsItem = null;

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
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param InteractionGet $interactionGetTax
     * @param TaxCalculation $taxCalculation
     * @param Config $config
     * @param TaxDetailsItem $taxDetailsItem
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        InteractionGet $interactionGetTax,
        TaxCalculation $taxCalculation,
        Config $config,
        TaxDetailsItem $taxDetailsItem,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->taxCalculation = $taxCalculation;
        $this->config = $config;
        $this->taxDetailsItem = $taxDetailsItem;
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $storeId = $quote->getStoreId();
        $postcode = $shippingAssignment->getShipping()->getAddress()->getPostcode();
        // If postcode is not present, then collect totals is being run from a context where customer has not submitted
        // their address, such as on the product listing, product detail, or cart page. Once the user enters their
        // postcode in the "Estimate Shipping & Tax" form on the cart page, or submits their shipping address in the
        // checkout, then a postcode will be present.
        // This will allow a merchant to configure default tax settings for their site using Magento's core tax
        // calculation and AvaTax's calculation will only kick in during cart/checkout. This is useful for countries
        // where merchants are required to display prices including tax (such as some countries that charge VAT tax).
        if (!$this->config->isModuleEnabled($storeId) || !$postcode) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $taxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, false);
        $baseTaxQuoteDetails = $this->getTaxQuoteDetails($shippingAssignment, $total, true);

        // Get tax from AvaTax API
        $taxDetailsList = $this->interactionGetTax->getTax($taxQuoteDetails, $baseTaxQuoteDetails, $shippingAssignment, $quote);

        if (!$taxDetailsList) {
            switch ($this->config->getErrorAction($quote->getStoreId())) {
                case Config::ERROR_ACTION_DISABLE_CHECKOUT:
                    $errorMessage = $this->config->getErrorActionDisableCheckoutMessage($quote->getStoreId());
                    // TODO: This exception gets caught by the last try/catch block in \Magento\Checkout\Model\ShippingInformationManagement::saveAddressInformation, so getting our custom exception message to display to user will take a different approach
                    throw new RemoteServiceUnavailableException($errorMessage);
                    break;
                case Config::ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX:
                    return parent::collect($quote, $shippingAssignment, $total);
                    break;
                case Config::ERROR_ACTION_ALLOW_CHECKOUT_NO_TAX:
                default:
                    // TODO: Get this fully working, as prices "including taxes" are still showing in checkout, and "Subtotal" is displaying $0. Reference how M1 does this.
                    $this->clearValues($total);
                    return null;
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
     * Map an item to item data object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Item\AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function mapItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $quoteDetailsItem = parent::mapItem($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);

        // TODO: Pass store ID
        if (!$this->config->isModuleEnabled()) {
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
     * Call tax calculation service to get tax details on the quote and items
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

    protected function addInfoToQuoteDetailsItemForShipping(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject)
    {
        $itemCode = \ClassyLlama\AvaTax\Framework\Interaction\Line::SHIPPING_LINE_TAX_CODE;
        $itemDescription =

        $this->addExtensionAttributesToTaxQuoteDetailsItem(
            $shippingDataObject,
            $itemCode,
            // TODO: Move this constant into this class?
            \ClassyLlama\AvaTax\Framework\Interaction\Line::SHIPPING_LINE_DESCRIPTION
        );
        return $this;
    }

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
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Item\AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItemExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
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

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case Giftwrapping::ITEM_TYPE:
                    // TODO: Pass store
                    $itemCode = $this->config->getSkuShippingGiftWrapItem();
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        // TODO: Move this constant into this class?
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ITEM_LINE_DESCRIPTION
                    );
                    break;
            }
        }

        return $itemDataObjects;
    }

    /**
     * Map extra taxables associated with quote
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapQuoteExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Address $address,
        $useBaseCurrency
    ) {
        $itemDataObjects = parent::mapQuoteExtraTaxables(
            $itemDataObjectFactory,
            $address,
            $useBaseCurrency
        );

        foreach ($itemDataObjects as $itemDataObject) {
            switch ($itemDataObject->getType()) {
                case Giftwrapping::QUOTE_TYPE:
                    // TODO: Pass store
                    $itemCode = $this->config->getSkuGiftWrapOrder();
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        // TODO: Move this constant into this class?
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
                case Giftwrapping::PRINTED_CARD_TYPE:
                    // TODO: Pass store
                    $itemCode = $this->config->getSkuShippingGiftWrapCard();
                    $this->addExtensionAttributesToTaxQuoteDetailsItem(
                        $itemDataObject,
                        $itemCode,
                        // TODO: Move this constant into this class?
                        \ClassyLlama\AvaTax\Framework\Interaction\Line::GIFT_WRAP_ORDER_LINE_DESCRIPTION
                    );
                    break;
            }
        }

        return $itemDataObjects;
    }
}
