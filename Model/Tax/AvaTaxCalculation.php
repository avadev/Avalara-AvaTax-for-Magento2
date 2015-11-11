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

namespace ClassyLlama\AvaTax\Model\Tax;

use Magento\Tax\Model\TaxDetails\TaxDetails;
use AvaTax\GetTaxResult;

class AvaTaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
    /**
     * Calculates tax for each of the items in a quote/order/invoice/creditmemo
     *
     * @param $data
     * @param GetTaxResult $getTaxResult
     * @param bool $useBaseCurrency
     * @param null $storeId
     * @param bool|true $round
     * @return mixed
     */
    public function calculateTaxDetails(
        $data,
        GetTaxResult $getTaxResult,
        // Add support for this argument
        $useBaseCurrency,
        $storeId = null,
        // TODO: Use or remove this argument
        $round = true
    ) {
        // Much of this code taken from Magento\Tax\Model\TaxCalculation::calculateTax
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
        $processedItems = [];
        /** @var Item $quoteItem */
        // TODO: Add support for object types other than quote
        foreach ($data->getAllItems() as $quoteItem) {
            $processedItem = $this->interactionGetTax->getTaxDetailsItem($quoteItem, $getTaxResult);

            // Items that are children of other items won't have tax data
            if (!$processedItem) {
                continue;
            }

            $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
            $processedItems[$quoteItem->getTaxCalculationItemId()] = $processedItem;
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
}
