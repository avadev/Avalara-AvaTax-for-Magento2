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
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;

class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
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
     * @var QuoteDetailsItemExtensionFactory
     */
    protected $extensionFactory;

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
     * @param QuoteDetailsItemExtensionFactory $extensionFactory
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
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        QuoteDetailsItemExtensionFactory $extensionFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
        $this->extensionFactory = $extensionFactory;
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

        $items = $taxQuoteDetails->getItems();
        $keyedItems = $this->getKeyedItems($items);
        $childrenItems = $this->getChildrenItems($items);

        $processedItems = [];
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($keyedItems as $item) {
            if (isset($childrenItems[$item->getCode()])) {
                $processedChildren = [];
                foreach ($childrenItems[$item->getCode()] as $child) {
                    $processedItem = $this->getTaxDetailsItem($child, $getTaxResult, $useBaseCurrency, $round);
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
                $processedItem = $this->getTaxDetailsItem($item, $getTaxResult, $useBaseCurrency, $round);
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
     * @param bool $useBaseCurrency
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function getTaxDetailsItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item,
        GetTaxResult $getTaxResult,
        $useBaseCurrency,
        $round
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

        $extensionAttributes = $item->getExtensionAttributes();
        if ($extensionAttributes) {
            $quantity = $extensionAttributes->getTotalQuantity() !== null
                ? $extensionAttributes->getTotalQuantity()
                : $item->getQuantity();
        } else {
            $quantity = $item->getQuantity();
        }

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
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

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

    /**
     * Get array of children items grouped by parent code
     *
     * This method handles the children grouping that is patterned off of
     * @see \Magento\Tax\Model\TaxCalculation::computeRelationships()
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return array
     */
    public function getChildrenItems($items)
    {
        $parentToChildren = [];
        foreach ($items as $item) {
            if ($item->getParentCode() !== null) {
                $parentToChildren[$item->getParentCode()][] = $item;
            }
        }
        return $parentToChildren;
    }

    /**
     * Get array of non-children items grouped by code
     *
     * This method handles the non-children grouping that is patterned off of
     * @see \Magento\Tax\Model\TaxCalculation::computeRelationships()
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return array
     */
    public function getKeyedItems($items)
    {
        $keyedItems = [];
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($items as $item) {
            if ($item->getParentCode() === null) {
                $keyedItems[$item->getCode()] = $item;
            }
        }
        return $keyedItems;
    }

    /**
     * Calculate the total quantity for all items and set the total quantity on the extension attribute object
     *
     * Total quantities are calculated here because quantity is sometimes determined by multiplying
     * child * parent quantity, so it's necessary to have all items in order to calculate this.
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     * @return $this
     */
    public function calculateTotalQuantities($items)
    {
        $keyedItems = $this->getKeyedItems($items);
        $childrenItems = $this->getChildrenItems($items);

        $processedItems = [];
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($keyedItems as $item) {
            if (isset($childrenItems[$item->getCode()])) {
                /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $childItem */
                foreach ($childrenItems[$item->getCode()] as $childItem) {
                    $processedItems[] = $childItem;
                }
            }
            $processedItems[] = $item;

        }
        foreach ($processedItems as $processedItem) {
            $extensionAttribute = $processedItem->getExtensionAttributes()
                ? $processedItem->getExtensionAttributes()
                : $this->extensionFactory->create();
            $totalQuantity = $this->calculateTotalQuantity($processedItem, $keyedItems);
            $extensionAttribute->setTotalQuantity($totalQuantity);

            $processedItem->setExtensionAttributes($extensionAttribute);
        }

        return $this;
    }

    /**
     * Calculates the total quantity for this item.
     *
     * What this really means is that if this is a child item, it return the parent quantity times
     * the child quantity and return that as the child's quantity. This code is a duplicate of the
     * @see \Magento\Tax\Model\TaxCalculation::getTotalQuantity()
     * method, but is refactored to accept the $keyedItems array.
     *
     * @param QuoteDetailsItemInterface $item
     * @param array $keyedItems
     * @return float
     */
    public function calculateTotalQuantity(QuoteDetailsItemInterface $item, array $keyedItems)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $keyedItems[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }
}
