<?php
/**
 * AvaTaxCalculation.php
 *
 * This code is separated into its own class as it uses specific methods of its parent class
 *
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Erik Hansen <erik@classyllama.com>
 * @copyright   Copyright (c) 2015 Erik Hansen & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\GetTaxResult;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Config;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;

class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
    use TaxCalculationUtility;

    /**
     * Prefix for applied taxes ID
     */
    const APPLIED_TAXES_ID_PREFIX = 'avatax-';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AppliedTaxInterfaceFactory
     */
    protected $appliedTaxDataObjectFactory;

    /**
     * @var AppliedTaxRateInterfaceFactory
     */
    protected $appliedTaxRateDataObjectFactory;

    /**
     * Constructor
     *
     * @param Calculation $calculation
     * @param CalculatorFactory $calculatorFactory
     * @param Config $config
     * @param TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param TaxClassManagementInterface $taxClassManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        PriceCurrencyInterface $priceCurrency,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
        return parent::__construct(
            $calculation,
            $calculatorFactory,
            $config,
            $taxDetailsDataObjectFactory,
            $taxDetailsItemDataObjectFactory,
            $storeManager,
            $taxClassManagement,
            $dataObjectHelper
        );
    }

    /**
     * Calculates tax for each of the items in a quote/order/invoice/creditmemo
     *
     * This code is heavily influenced by this method:
     * @see Magento\Tax\Model\TaxCalculation::calculateTax()
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @param null $storeId
     * @param bool|true $round
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    public function calculateTaxDetails(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        GetTaxResult $getTaxResult,
        $useBaseCurrency,
        $storeId = null,
        // TODO: Use or remove this argument
        $round = true
    ) {
        if ($storeId === null) {
            // TODO: Use or remove this method
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        // initial TaxDetails data
        $taxDetailsData = [
            TaxDetails::KEY_SUBTOTAL => 0.0,
            TaxDetails::KEY_TAX_AMOUNT => 0.0,
            TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            TaxDetails::KEY_APPLIED_TAXES => [],
            TaxDetails::KEY_ITEMS => [],
        ];

        $this->computeRelationships($taxQuoteDetails->getItems());

        $processedItems = [];
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($this->keyedItems as $item) {
            if (isset($this->parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($this->parentToChildren[$item->getCode()] as $child) {
                    $processedItem = $this->getTaxDetailsItem($child, $getTaxResult, $useBaseCurrency);
                    if ($processedItem) {
                        $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
                        $processedItems[$processedItem->getCode()] = $processedItem;
                        $processedChildren[] = $processedItem;
                    }
                }
                $processedItem = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItem->setCode($item->getCode());
                $processedItem->setType($item->getType());
            } else {
                $processedItem = $this->getTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
              $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
                if ($processedItem) {
                    $processedItems[$processedItem->getCode()] = $processedItem;
                }
            }
            $processedItems[$processedItem->getCode()] = $processedItem;
        }

        $taxDetailsDataObject = $this->taxDetailsDataObjectFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $taxDetailsDataObject,
            $taxDetailsData,
            '\Magento\Tax\Api\Data\TaxDetailsInterface'
        );
        $taxDetailsDataObject->setItems($processedItems);
        return $taxDetailsDataObject;
    }

    /**
     * Convert a quote/order/invoice/credit memo item to a tax details item objects
     *
     * This includes tax for the item as well as any additional line item tax information like Gift Wrapping
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function getTaxDetailsItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item,
        GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        // TODO: Get store
        $store = null;

        $price = $item->getUnitPrice();

        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine($item->getCode());

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        // TODO: Add support for this
        $discountTaxCompensationAmount  = 0;

        $quantity = $this->getTotalQuantity($item);
        $rowTotal = $price * $quantity;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax, $store);
        }

        $rowTotalInclTax = $rowTotal + $rowTax;

        $priceInclTax = $rowTotalInclTax / $quantity;
        // TODO: Implement rounding logic
        //if ($round) {
        //    $priceInclTax = $this->calculationTool->round($priceInclTax);
        //}

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }

    /**
     * Get the associated tax rates that were applied to a quote/order/invoice/creditmemo item
     *
     * @param GetTaxResult $getTaxResult
     * @param float $rowTax
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[]
     */
    protected function getAppliedTaxes(
        GetTaxResult $getTaxResult,
        $rowTax
    ) {
        $appliedTaxDataObjects = [];

        foreach ($getTaxResult->getTaxSummary() as $key => $row) {
            /* @var \AvaTax\TaxDetail $row */
            $percent = (float)($row->getRate() * Tax::RATE_MULTIPLIER);

            $appliedTaxDataObject = $this->appliedTaxDataObjectFactory->create();
            // TODO: Should we use the total tax amount ($row->getTax()) anywhere?
            $appliedTaxDataObject->setAmount($rowTax);
            $appliedTaxDataObject->setPercent($percent);
            $appliedTaxDataObject->setTaxRateKey($row->getTaxName());

            /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rateDataObjects */
            $rateDataObjects = [];

            $id = self::APPLIED_TAXES_ID_PREFIX . $key;
            // Skipped position, priority and rule_id
            $rateDataObjects[$id] = $this->appliedTaxRateDataObjectFactory->create()
                ->setPercent($percent)
                ->setCode($row->getTaxName())
                ->setTitle($row->getTaxName());
            $appliedTaxDataObject->setRates($rateDataObjects);

            $appliedTaxDataObjects[] = $appliedTaxDataObject;
        }
        return $appliedTaxDataObjects;
    }
}
