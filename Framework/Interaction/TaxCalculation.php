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
     * @param AppliedTaxRateExtensionFactory $appliedTaxRateExtensionFactory
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
        QuoteDetailsItemExtensionFactory $extensionFactory,
        AppliedTaxRateExtensionFactory $appliedTaxRateExtensionFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
        $this->extensionFactory = $extensionFactory;
        $this->appliedTaxRateExtensionFactory = $appliedTaxRateExtensionFactory;
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
     * There are rare situations in which the Tax Summary from AvaTax will contain items that have the same TaxName,
     * JurisCode, JurisName, and Rate. e.g., an order with shipping with VAT tax from Germany (example below).
     * To account for this, we need to group rates by a combination of JurisCode and JurisName. Otherwise the same
     * rate will get added twice. This is problematic for two reasons:
     *     1. If a merchant has configured Magento to "Display Full Tax Summary" then the user will see the same
     *        same rate with the same percentage displayed twice. This will be confusing.
     *     2. When an order is placed, the \Magento\Tax\Model\Plugin\OrderSave::saveOrderTax method populates the
     *        sales_order_tax[_item] tables with information based on the information contained in the Applied Taxes
     *        array. Having duplicates rates will throw things off.
     *
     * 'TaxSummary' => ['TaxDetail' => [
     *     // This rate was applied to shipping
     *     0 => [
     *         'JurisType' => 'State',
     *         'JurisCode' => 'DE',
     *         'TaxType' => 'Sales',
     *         'Taxable' => '20',
     *         'Rate' => '0.189995',
     *         'Tax' => '3.8',
     *         'JurisName' => 'GERMANY',
     *         'TaxName' => 'Standard Rate',
     *         'Country' => 'DE',
     *         'Region' => 'DE',
     *         'TaxCalculated' => '3.8',
     *     ],
     *     // This rate was applied to products
     *     1 => [
     *         'JurisType' => 'State',
     *         'JurisCode' => 'DE',
     *         'TaxType' => 'Sales',
     *         'Base' => '150',
     *         'Taxable' => '150',
     *         'Rate' => '0.190000',
     *         'Tax' => '28.5',
     *         'JurisName' => 'GERMANY',
     *         'TaxName' => 'Standard Rate',
     *         'Country' => 'DE',
     *         'Region' => 'DE',
     *         'TaxCalculated' => '28.5',
     *     ]
     * ]]
     *
     * @param \Magento\Framework\DataObject $getTaxResult
     *
     * @return array
     */
    protected function getTaxRatesByCode($getTaxResult)
    {
        $taxRatesByCode = [];

        $customsTaxTypes = ['Customs', 'LandedCost'];

        /* @var \Magento\Framework\DataObject $row */
        foreach ($getTaxResult->getSummary() as $key => $row) {
            $arrayKey = "{$row->getJurisCode()}_{$row->getJurisName()}_{$row->getTaxType()}";
            $isCustomsTax = \in_array($row->getTaxType(), $customsTaxTypes);
            $rate = (float)$row->getRate();

            /**
             * Magento requires there to be a percentage rate in order to save the taxes to the sales_order_tax table
             * so we need to calculate a rate that isn't completely bogus (since the one from AvaTax is bogus)
             *
             * @see vendor/magento/module-tax/Model/Plugin/OrderSave.php:134
             */
            if ($isCustomsTax) {
                $rate = (float)$row->getTax() / (float)$getTaxResult->getTotalAmount();
            }

            // Since the total percent is for display purposes only, round to 5 digits. Since the tax percent returned
            // from AvaTax is not the actual tax rate, but the effective rate, rounding makes the presentation make more
            // sense to the user. For example, a tax rate may be 19%, but AvaTax may return a value of 0.189995.
            $ratePercent = (round($rate, 4) * Tax::RATE_MULTIPLIER);

            /**
             * There are rare situations in which a duplicate rate will have a slightly different percentage (see
             * example in DocBlock above). In these cases, we will just determine the "effective" rate" ourselves.
             *
             * In addition, Customs should be merged together
             */
            if (isset($taxRatesByCode[$arrayKey]) && ($isCustomsTax || $taxRatesByCode[$arrayKey]['ratePercent'] != $ratePercent)) {
                $taxRatesByCode[$arrayKey]['taxable'] += (float)$row->getTaxable();
                $taxRatesByCode[$arrayKey]['tax'] += (float)$row->getTax();

                // Avoid division by 0; the ratePercent is only used in the rare situation where there are different
                // percents
                if ($taxRatesByCode[$arrayKey]['taxable'] > 0) {
                    $blendedRate = $taxRatesByCode[$arrayKey]['tax'] / $taxRatesByCode[$arrayKey]['taxable'];
                    $taxRatesByCode[$arrayKey]['ratePercent'] = $blendedRate;
                }

                continue;
            }

            $taxRatesByCode[$arrayKey] = [
                // In case jurisdiction codes are duplicated, prepending the $key ensures we have a unique ID
                'id' => $key . '_' . $row->getJurisCode(),
                'ratePercent' => $ratePercent,
                'taxName' => $isCustomsTax ? __('Customs Duty and Import Tax') : $row->getTaxName(),
                // Prepend a string to the juris code to prevent false positives on comparison (e.g. '053' == '53)
                // Also, append the tax type to prevent Magento from trying to create multiple tax rates to the same
                // item
                'jurisCode' => "AVATAX-{$row->getJurisCode()}-{$row->getTaxType()}",
                // These two values will only be used in the conditional below
                'taxable' => (float)$row->getTaxable(),
                'tax' => (float)$row->getTax(),
            ];
        }

        return $taxRatesByCode;
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
    ) {
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
     * @param QuoteDetailsItemInterface $item
     * @param TaxResult $getTaxResult
     * @param bool $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface $scope
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|bool
     * @throws LocalizedException
     */
    protected function getTaxDetailsItem(
        QuoteDetailsItemInterface $item,
        $getTaxResult,
        $useBaseCurrency,
        $scope
    ) {
        $price = $item->getUnitPrice();

        /* @var $taxLine \Magento\Framework\DataObject  */
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
         * \Magento\Framework\Pricing\PriceCurrencyInterface::convert() method. However if we simply convert the AvaTax
         * base tax amount * currency multiplier, we may run into issues due to rounding.
         *
         * For example, a $9.90 USD base price * a 6% tax rate equals a tax amount of $0.59 (.594 rounded). Assume the
         * current currency has a conversion rate of 2x. The price will display to the user as $19.80. There are two
         * ways we can calculate the tax amount:
         * 1. Multiply the tax amount received back from AvaTax, which would be $1.18 ($0.59 * 2).
         * 2. Multiply using this formula (base price * currency rate) * tax rate) ((9.99 * 2) * .06)
         *    which would be $1.19 (1.188 rounded)
         *
         * The second approach is more accurate and is what we are doing here.
         */
        if (!$useBaseCurrency) {
            /**
             * We could recalculate the amount using the same logic found in this class:
             * @see \ClassyLlama\AvaTax\Framework\Interaction\Line::convertTaxQuoteDetailsItemToData,
             * but using the taxable amount returned back from AvaTax is the only way to get an accurate amount as
             * some items sent to AvaTax may be tax exempt
             */
            $baseTaxableAmount = (float)$taxLine->getTaxableAmount();
            $taxableAmount = $this->priceCurrency->convert($baseTaxableAmount, $scope);

            $tax = $taxableAmount * $rate;
            $tax = $this->calculationTool->round($tax);
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
            $quantity = $extensionAttributes->getTotalQuantity() !== null
                ? $extensionAttributes->getTotalQuantity()
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

        /**
         * The \Magento\Tax\Model\Calculation\AbstractAggregateCalculator::calculateWithTaxNotInPrice method that this
         * method is patterned off of has $round as a variable, but any time that method is used in the context of a
         * collect totals on a quote, rounding is always used.
         */
        $round = true;
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

        $appliedTax = $this->getAppliedTax($getTaxResult, $rowTax);
        $appliedTaxes = [
            $appliedTax->getTaxRateKey() => $appliedTax
        ];

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
            ->setAppliedTaxes($appliedTaxes);
    }

    /**
     * Convert the AvaTax Tax Summary to a Magento object
     *
     * @see \Magento\Tax\Model\Calculation\AbstractCalculator::getAppliedTax()
     *
     * @param \Magento\Framework\DataObject $getTaxResult
     * @param float $rowTax
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface
     */
    protected function getAppliedTax(
        $getTaxResult,
        $rowTax
    ) {
        $totalPercent = 0.00;
        $taxNames = [];

        /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rateDataObjects */
        $rateDataObjects = [];

        foreach ($this->getTaxRatesByCode($getTaxResult) as $rowArray) {
            $ratePercent = $rowArray['ratePercent'];
            $totalPercent += $ratePercent;
            $taxCode = $rowArray['jurisCode'];
            $taxName = $rowArray['taxName'];
            $taxNames[] = $rowArray['taxName'];
            $taxable = $rowArray['taxable'];
            $tax = $rowArray['tax'];
            // In case jurisdiction codes are duplicated, prepending the $key ensures we have a unique ID
            $id = $rowArray['id'];

            // Add row-specific tax amounts as extension attribute
            $appliedTaxRateExtension = $this->appliedTaxRateExtensionFactory
                ->create()
                ->setRatePercent($ratePercent === 0.0 ? null : $ratePercent)
                ->setTaxName($taxName)
                ->setJurisCode($taxCode)
                ->setTaxable($taxable)
                ->setTax($tax);

            // Skipped position, priority and rule_id
            $rateDataObjects[$id] = $this->appliedTaxRateDataObjectFactory->create()
                ->setPercent($ratePercent === 0.0 ? null : $ratePercent)
                ->setCode($taxCode)
                ->setTitle($taxName)
                ->setExtensionAttributes($appliedTaxRateExtension);
        }
        $rateKey = implode(' - ', $taxNames);

        $appliedTaxDataObject = $this->appliedTaxDataObjectFactory->create();
        $appliedTaxDataObject->setAmount($rowTax);
        $appliedTaxDataObject->setPercent($totalPercent);
        $appliedTaxDataObject->setTaxRateKey($rateKey);
        $appliedTaxDataObject->setRates($rateDataObjects);

        return $appliedTaxDataObject;
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
