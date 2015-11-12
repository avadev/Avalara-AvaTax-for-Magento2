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
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Model\Calculation;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\DataObject;
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
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
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
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->avaTaxCalculation = $avaTaxCalculation;
        $this->config = $config;
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
        if (!$this->config->isModuleEnabled($storeId)) {
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

        // TODO: Add individual support for calculating base vs normal rates
        $baseTaxDetails = $this->avaTaxCalculation->calculateTaxDetails($quote, $getTaxResult, false);
        $taxDetails = $this->avaTaxCalculation->calculateTaxDetails($quote, $getTaxResult, true);

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

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

        // TODO: Figure out whether this should be removed/refactored
        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }

        return $this;
    }
}
