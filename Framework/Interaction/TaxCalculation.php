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

use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as TaxResult;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateExtensionFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use Magento\Framework\Api\DataObjectHelper;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelper;

class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
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
     * @var AppliedTaxRateExtensionFactory
     */
    protected $appliedTaxRateExtensionFactory;

    /**
     * Rate that will be used instead of 0, as using 0 causes tax rates to not save
     */
    const DEFAULT_TAX_RATE = -0.001;

    /**
     * @var AvaTaxHelper
     */
    protected $avaTaxHelper;

    /**
     * Constructor
     *
     * @param Calculation                             $calculation
     * @param CalculatorFactory                       $calculatorFactory
     * @param Config                                  $config
     * @param TaxDetailsInterfaceFactory              $taxDetailsDataObjectFactory
     * @param TaxDetailsItemInterfaceFactory          $taxDetailsItemDataObjectFactory
     * @param StoreManagerInterface                   $storeManager
     * @param TaxClassManagementInterface             $taxClassManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param PriceCurrencyInterface                  $priceCurrency
     * @param AppliedTaxInterfaceFactory              $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory          $appliedTaxRateDataObjectFactory
     * @param QuoteDetailsItemExtensionFactory        $extensionFactory
     * @param AppliedTaxRateExtensionFactory          $appliedTaxRateExtensionFactory
     * @param AvaTaxHelper                            $avaTaxHelper
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement,
        DataObjectHelper $dataObjectHelper,
        PriceCurrencyInterface $priceCurrency,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        QuoteDetailsItemExtensionFactory $extensionFactory,
        AppliedTaxRateExtensionFactory $appliedTaxRateExtensionFactory,
        AvaTaxHelper $avaTaxHelper
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
        $this->extensionFactory = $extensionFactory;
        $this->appliedTaxRateExtensionFactory = $appliedTaxRateExtensionFactory;
        $this->avaTaxHelper = $avaTaxHelper;

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
     * Calculates tax for each of the items in a quote/order/invoice/credit memo
     *
     * This code is heavily influenced by this method:
     * @see \Magento\Tax\Model\TaxCalculation::calculateTax()
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Framework\DataObject               $getTaxResult
     * @param bool                                        $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface       $scope
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     * @throws LocalizedException
     */
    public function calculateTaxDetails(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        $getTaxResult,
        $useBaseCurrency,
        $scope
    )
    {
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
                    $processedItem = $this->getTaxDetailsItem($child, $getTaxResult, $useBaseCurrency, $scope);
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
                $processedItem = $this->getTaxDetailsItem($item, $getTaxResult, $useBaseCurrency, $scope);
                if ($processedItem) {
                    $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
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
     * @param QuoteDetailsItemInterface             $item
     * @param TaxResult                             $getTaxResult
     * @param bool                                  $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface $scope
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|bool
     * @throws LocalizedException
     */
    protected function getTaxDetailsItem(
        QuoteDetailsItemInterface $item,
        $getTaxResult,
        $useBaseCurrency,
        $scope
    )
    {
        $price = $item->getUnitPrice();

        /* @var $taxLine \Magento\Framework\DataObject */
        $taxLine = $getTaxResult->getTaxLine($item->getCode());

        // Items that are children of other items won't have lines in the response
        if (is_null($taxLine)) {
            return false;
        }

        $rate = $getTaxResult->getLineRate($taxLine);
        $tax = (float)$taxLine->getTax();

        /**
         * Magento uses base rates for determining what to charge a customer, not the currency rate (i.e., the non-base
         * rate). Because of this, the base amounts are what is being sent to AvaTax for rate calculation. When we get
         * the base tax amounts back from AvaTax, we have to convert those to the current store's currency using the
         * \Magento\Framework\Pricing\PriceCurrencyInterface::convert() method.
         */
        if (!$useBaseCurrency) {
            $tax = $this->priceCurrency->convert($tax, $scope);
        }

        $rowTax = $tax;

        /**
         * In native Magento, the "row_total_incl_tax" and "base_row_total_incl_tax" fields contain the tax before
         * discount. The AvaTax 15 API doesn't have the concept of before/after discount tax, so in order to determine
         * the "before discount tax amount", we need to multiply the discount by the rate returned by AvaTax.
         * @see \Magento\Tax\Model\Calculation\AbstractAggregateCalculator::calculateWithTaxNotInPrice
         *
         * If the rate is 0, then this product doesn't have taxes applied and tax on discount shouldn't be calculated.
         * If tax is 0, then item was tax-exempt for some reason and tax on discount shouldn't be calculated
         */
        if ($rate > 0 && $tax > 0) {
            /**
             * Accurately calculating what AvaTax would have charged before discount requires checking to see if any
             * of the tax amount is tax exempt. If so, we need to find out what percentage of the total amount AvaTax
             * deemed as taxable and then use that percentage when calculating the discount amount. This partially
             * taxable scenario can arise in a situation like this:
             * @see https://help.avalara.com/kb/001/Why_is_freight_taxed_partially_on_my_sale
             *
             * To test this functionality, you can create a "Base Override" Tax Rule in the AvaTax admin to mark certain
             * jurisdictions as partially taxable.
             */
            $taxableAmountPercentage = 1;
            if ($taxLine->getExemptAmount() > 0) {
                // This value is the total amount sent to AvaTax for tax calculation, before AvaTax determined what
                // portion of the amount is taxable
                $totalAmount = ($taxLine->getTaxableAmount() + $taxLine->getExemptAmount());
                // Avoid division by 0
                if ($totalAmount != 0) {
                    $taxableAmountPercentage = $taxLine->getTaxableAmount() / $totalAmount;
                }
            }

            $effectiveDiscountAmount = $taxableAmountPercentage * $item->getDiscountAmount();
            $taxOnDiscountAmount = $effectiveDiscountAmount * $rate;
            $taxOnDiscountAmount = $this->calculationTool->round($taxOnDiscountAmount);
            $rowTaxBeforeDiscount = $rowTax + $taxOnDiscountAmount;
        } else {
            $rowTaxBeforeDiscount = 0;
        }

        $extensionAttributes = $item->getExtensionAttributes();
        if ($extensionAttributes) {
            $quantity = $extensionAttributes->getTotalQuantity() !== null ? $extensionAttributes->getTotalQuantity()
                : $item->getQuantity();
        } else {
            $quantity = $item->getQuantity();
        }
        $rowTotal = $price * $quantity;
        $rowTotalInclTax = $rowTotal + $rowTaxBeforeDiscount;
        $priceInclTax = $rowTotalInclTax / $quantity;

        /**
         * Since the AvaTax extension does not support merchants adding products with tax already factored into the
         * price, we don't need to do any calculations for this number. The only time this value would be something
         * other than 0 is when this method runs:
         * @see \Magento\Tax\Model\Calculation\AbstractAggregateCalculator::calculateWithTaxInPrice
         */
        $discountTaxCompensationAmount = 0;
        $taxIncluded = $this->avaTaxHelper->getTaxationPolicy($scope);
        if ($taxIncluded && $rowTax > 0) {
            $discountTaxCompensationAmount = -$rowTax;
        }

        /**
         * The \Magento\Tax\Model\Calculation\AbstractAggregateCalculator::calculateWithTaxNotInPrice method that this
         * method is patterned off of has $round as a variable, but any time that method is used in the context of a
         * collect totals on a quote, rounding is always used.
         */
        $round = true;
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

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
            ->setTaxPercent($rate * Tax::RATE_MULTIPLIER)
            ->setAppliedTaxes($this->getAppliedTaxes($taxLine, $useBaseCurrency, $scope));
    }

    /**
     * Convert each line item detail into Magento Applied Taxes
     *
     * @see \Magento\Tax\Model\Calculation\AbstractCalculator::getAppliedTax()
     *
     * @param \Magento\Framework\DataObject         $lineItem
     * @param bool                                  $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface $scope
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[]
     */
    protected function getAppliedTaxes($lineItem, $useBaseCurrency, $scope)
    {
        $appliedTaxDataObjects = [];
        $customsTaxTypes = ['Customs', 'LandedCost'];

        /* @var \Magento\Framework\DataObject $lineItemDetail */
        foreach ($lineItem->getData('details') as $index => $lineItemDetail) {
            $jurisdictionCode = $lineItemDetail->getData('juris_code');
            $jurisdictionName = $lineItemDetail->getData('juris_name');
            $jurisdictionType = $lineItemDetail->getData('juris_type');
            $taxType = $lineItemDetail->getData('tax_type');
            $taxSubTypeId = $lineItemDetail->getData('tax_sub_type_id');
            $taxTitle = $lineItemDetail->getData('tax_name');
            $rate = (float)$lineItemDetail->getData('rate');

            // Rename the tax title to our own label
            if (\in_array($taxType, $customsTaxTypes)) {
                $taxTitle = (string)__('Duty');
            }

            $taxableAmount = (float)$lineItemDetail->getData('taxable_amount');
            $taxCalculated = (float)$lineItemDetail->getData('tax_calculated');
            $tax = (float)$lineItemDetail->getData('tax');

            if (!$useBaseCurrency) {
                $tax = $this->priceCurrency->convert($tax, $scope);
            }

            /**
             * Magento requires there to be a percentage rate in order to save the taxes to the sales_order_tax table
             * so we need to calculate a rate that isn't completely bogus (since the one from AvaTax is bogus). Also,
             * due to Magento incorrectly using == to evaluate NULL to 0, we can't use NULL either
             *
             * @see https://github.com/magento/magento2/blob/2.2/app/code/Magento/Tax/Model/Plugin/OrderSave.php#L134
             * @see https://github.com/magento/magento2/blob/2.2/app/code/Magento/Tax/Model/Sales/Total/Quote/CommonTaxCollector.php#L764
             *
             * Also, even if Magento didn't incorrectly use the == comparision, AvaTax would still break as Magento
             * would expect tax values to come from the rates instead of the applied tax:
             * @see https://github.com/magento/magento2/blob/2.2/app/code/Magento/Tax/Model/Sales/Total/Quote/CommonTaxCollector.php#L784
             *
             * Which, by the way, if you were to try and support, amount and base_amount are not even supported by
             * the AppliedTaxRateInterface, which I assume is why the CommonTaxCollector relies on:
             * @see https://github.com/magento/magento2/blob/2.2/app/code/Magento/Tax/Api/Data/AppliedTaxRateInterface.php
             */
            if ($rate === 0.0 && $taxCalculated > 0) {
                $rate = $taxableAmount > 0 && $taxCalculated > 0 ? $taxCalculated / $taxableAmount
                    : self::DEFAULT_TAX_RATE;
            }

            // Normalize the AvaTax rate to a Magento rate
            $rate *= Tax::RATE_MULTIPLIER;
            // Generate an array key to represent this tax item to be summed across line items
            $arrayKey = "{$jurisdictionCode}_{$jurisdictionName}_{$taxType}_{$taxSubTypeId}_{$rate}_{$jurisdictionType}";
            $appliedTaxDataObjects[$arrayKey] = $this->appliedTaxDataObjectFactory->create(
                [
                    'data' => [
                        'amount' => $tax,
                        'percent' => $rate,
                        'tax_rate_key' => $arrayKey,
                        // Include at least one rate
                        'rates' => [
                            $this->appliedTaxRateDataObjectFactory->create(
                                [
                                    'data' => [
                                        'percent' => $rate,
                                        'code' => "AVATAX-{$jurisdictionCode}-{$taxType}-{$rate}",
                                        'title' => $taxTitle
                                    ]
                                ]
                            )
                        ]
                    ]
                ]
            );
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
     *
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
     *
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
     *
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
            $extensionAttribute = $processedItem->getExtensionAttributes() ? $processedItem->getExtensionAttributes()
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
     * @param array                     $keyedItems
     *
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
