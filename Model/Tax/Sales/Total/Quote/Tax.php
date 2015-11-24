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
use ClassyLlama\AvaTax\Model\Tax\AvaTaxCalculation;
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
     * @var AvaTaxCalculation
     */
    protected $avaTaxCalculation = null;

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
     * @param AvaTaxCalculation $avaTaxCalculation
     * @param Config $config
     * @param TaxDetailsItem $taxDetailsItem
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
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
        AvaTaxCalculation $avaTaxCalculation,
        Config $config,
        TaxDetailsItem $taxDetailsItem,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->avaTaxCalculation = $avaTaxCalculation;
        $this->config = $config;
        $this->taxDetailsItem = $taxDetailsItem;
        $this->dataObjectHelper = $dataObjectHelper;
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

        // If postal code hasn't been provided, don't estimate tax
        if (!$quote->getShippingAddress()->getPostcode()) {
            return $this;
        }

        // Get tax from AvaTax API
        $getTaxResult = $this->interactionGetTax->getTax($quote);

        if (!$getTaxResult) {
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
        }

        $baseTaxDetails = $this->avaTaxCalculation->calculateTaxDetails($quote, $getTaxResult, true, $storeId);
        $taxDetails = $this->avaTaxCalculation->calculateTaxDetails($quote, $getTaxResult, false, $storeId);

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (count($this->taxDetailsItem->getGwItemCodeMapping())) {
            $total->setGwItemCodeToItemMapping($this->taxDetailsItem->getGwItemCodeMapping());
        }

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        // TODO: Handle shipping tax calculation
        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($shippingAssignment, $total, $shippingTaxDetails, $baseShippingTaxDetails);
        }


        // TODO: Figure out whether this should be removed/refactored
        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($total, $itemsByType);

        // TODO: Figure out whether this should be removed/refactored
        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }

        return $this;
    }
}
