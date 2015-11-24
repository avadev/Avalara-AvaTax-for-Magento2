<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping;

// TODO: Rename this class to something more semantically appropriate like TaxDetailsItemConverter or TaxDetailsItem/Get
class TaxDetailsItem
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var TaxDetailsItemInterfaceFactory
     */
    protected $taxDetailsItemDataObjectFactory;

    /**
     * @var AppliedTaxInterfaceFactory
     */
    protected $appliedTaxDataObjectFactory;

    /**
     * @var AppliedTaxRateInterfaceFactory
     */
    protected $appliedTaxRateDataObjectFactory;

    /**
     * Stores the mapping association between the arbitrary codes used for gw_item and the quotes
     * associated with them. Refer to the two @see methods below for how core Magento sets and uses this array
     *
     * @see Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::_collectWrappingForItems()
     * @see Magento\GiftWrapping\Model\Total\Quote\Tax\GiftwrappingAfterTax::processWrappingForItems()
     * @var array
     */
    protected $gwItemCodeToItemMapping = [];

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->taxDetailsItemDataObjectFactory = $taxDetailsItemDataObjectFactory;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
    }

    /**
     * Convert a quote/order/invoice/credit memo item to a tax details item objects
     *
     * This includes tax for the item as well as any additional line item tax information like Gift Wrapping
     *
     * @param $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function getTaxDetailsItems($item, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        switch (true) {
            case ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface):
                // TODO: Create this method
                return $this->convertOrderItemToTaxDetailsItems($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Quote\Api\Data\CartItemInterface):
                return $this->convertQuoteItemToTaxDetailsItems($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                // TODO: Create this method
                return $this->convertInvoiceItemToTaxDetailsItems($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                // TODO: Create this method
                return $this->convertCreditmemoItemToTaxDetailsItems($item, $getTaxResult, $useBaseCurrency);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Add quote item to gift wrap mapping array
     *
     * @param string $code
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return void
     */
    public function addGwItemCodeMapping($code, \Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $this->gwItemCodeToItemMapping[$code] = $item;
    }

    /**
     * Retrieve gift wrapping mapping array
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     */
    public function getGwItemCodeMapping()
    {
        return $this->gwItemCodeToItemMapping;
    }

    /**
     * Reset gift wrap mapping array. Important that this happens between separate API requests.
     *
     * @return void
     */
    public function resetGwItemCodeMapping()
    {
        $this->gwItemCodeToItemMapping = [];
    }

    /**
     * Convert quote item to tax details items. Returns TaxDetailsItem objects for both the quote tax as well as gift
     * wrapping tax
     * TODO: Refactor this, as having a method just to return the two child methods doesn't make sense
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function convertQuoteItemToTaxDetailsItems(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        \AvaTax\GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        $taxDetails = [];

        $taxInfo = $this->convertQuoteItemToTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
        if ($taxInfo) {
            $taxDetails[] = $taxInfo;
        }

        $giftWrapTaxInfo = $this->convertQuoteItemToGiftWrapTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
        if ($giftWrapTaxInfo) {
            $taxDetails[] = $giftWrapTaxInfo;
            $this->addGwItemCodeMapping($giftWrapTaxInfo->getCode(), $item);
        }

        return $taxDetails;
    }

    /**
     * Convert quote item to tax details item
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param bool $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function convertQuoteItemToTaxDetailsItem(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        \AvaTax\GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine($item->getId());

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        $quantity = $item->getQty(); // TODO: Add support for getting QTY from parent products See \Magento\Tax\Model\TaxCalculation::getTotalQuantity

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            if (!$item->getBaseTaxCalculationPrice()) {
                $item->setBaseTaxCalculationPrice($item->getBaseCalculationPriceOriginal());
            }
            $price = $item->getBaseTaxCalculationPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        } else {
            if (!$item->getTaxCalculationPrice()) {
                $item->setTaxCalculationPrice($item->getCalculationPriceOriginal());
            }
            $price = $item->getTaxCalculationPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }
        // TODO: Determine if we need to only round if certain admin settings are configured
        $price = $this->priceCurrency->round($price);

        // TODO: Determine if we need to round $rowTotal
        // TODO: Switch to logic from \ClassyLlama\AvaTax\Framework\Interaction\Line::convertQuoteItemToData
        $rowTotal = $price * $quantity;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getTaxCalculationItemId())
            ->setType('product') // TODO: Change to constant
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
     * Convert quote item to tax details item for gift wrapping
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param bool $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function convertQuoteItemToGiftWrapTaxDetailsItem(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        \AvaTax\GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine(Giftwrapping::CODE_ITEM_GW_PREFIX . $item->getId());

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        $quantity = $item->getQty(); // TODO: Add support for getting QTY from parent products See \Magento\Tax\Model\TaxCalculation::getTotalQuantity

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $item->getGwBasePrice();
        } else {
            $price = $item->getGwPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }

        // TODO: Determine if we need to round $rowTotal
        $rowTotal = $price * $quantity;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode(Giftwrapping::CODE_ITEM_GW_PREFIX . $item->getItemId())
            ->setType(Giftwrapping::CODE_ITEM_GW_PREFIX)
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getTaxCalculationItemId()) // TODO: Remove this item?
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }

    /**
     * Get all quote-level taxes and return them
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function getTaxDetailsItemsForQuote($data, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        $taxDetailsItems = [];

        $gwQuoteTaxDetailsItem = $this->getGwQuoteTaxDetailsItem($data, $getTaxResult, $useBaseCurrency);
        if ($gwQuoteTaxDetailsItem) {
            $taxDetailsItems[] = $gwQuoteTaxDetailsItem;
        }

        $gwCardTaxDetailsItem = $this->getGwCardTaxDetailsItem($data, $getTaxResult, $useBaseCurrency);
        if ($gwCardTaxDetailsItem) {
            $taxDetailsItems[] = $gwCardTaxDetailsItem;
        }

        $shippingTaxDetailsItem = $this->getShippingTaxDetailsItem($data, $getTaxResult, $useBaseCurrency);
        if ($shippingTaxDetailsItem) {
            $taxDetailsItems[] = $shippingTaxDetailsItem;
        }

        return $taxDetailsItems;
    }

    public function getGwQuoteTaxDetailsItem($data, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine(Giftwrapping::CODE_QUOTE_GW);

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $data->getShippingAddress()->getGwBasePrice(); // TODO: Should this have a price?
        } else {
            $price = $data->getShippingAddress()->getGwPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }

        // TODO: Determine if we need to round $rowTotal
        $rowTotal = $price;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode(Giftwrapping::CODE_QUOTE_GW)
            ->setType(Giftwrapping::QUOTE_TYPE) // Correct data?
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE)
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }

    public function getGwCardTaxDetailsItem($data, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine(Giftwrapping::CODE_PRINTED_CARD);

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $data->getShippingAddress()->getGwCardBasePrice();
        } else {
            $price = $data->getShippingAddress()->getGwCardPrice();
        }

        // TODO: Determine if we need to round $rowTotal
        $rowTotal = $price;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode(Giftwrapping::CODE_PRINTED_CARD)
            ->setType(Giftwrapping::PRINTED_CARD_TYPE)
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE)
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }

    public function getShippingTaxDetailsItem($data, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine(Line::SHIPPING_LINE_NO);

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $data->getShippingAddress()->getBaseShippingAmount(); // TODO: Should this have a price?
        } else {
            $price = $data->getShippingAddress()->getShippingAmount(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }
        // TODO: Determine if we need to only round if certain admin settings are configured
//        $price = $this->priceCurrency->round($price);

        // TODO: Determine if we need to round $rowTotal
        $rowTotal = $price;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode(CommonTaxCollector::ITEM_CODE_SHIPPING)
            ->setType(CommonTaxCollector::ITEM_TYPE_SHIPPING) // Correct data?
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
//            ->setAssociatedItemCode() // TODO: Figure out what should be set here
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }

    /**
     * Get the associated tax rates that were applied to a quote/order/invoice/creditmemo item
     *
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param float $rowTax
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[]
     */
    protected function getAppliedTaxes(
        \AvaTax\GetTaxResult $getTaxResult,
        $rowTax
    ) {
        $appliedTaxDataObjects = [];

        foreach ($getTaxResult->getTaxSummary() as $key => $row) {
            $percent = $row->getRate() * Tax::RATE_MULTIPLIER;

            $appliedTaxDataObject = $this->appliedTaxDataObjectFactory->create();
            // TODO: Should we use the total tax amount ($row->getTax()) anywhere?
            $appliedTaxDataObject->setAmount($rowTax);
            $appliedTaxDataObject->setPercent($percent);
            $appliedTaxDataObject->setTaxRateKey($row->getTaxName());

            /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rateDataObjects */
            $rateDataObjects = [];

            $id = 'avatax-' . $key;
            //Skipped position, priority and rule_id
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
