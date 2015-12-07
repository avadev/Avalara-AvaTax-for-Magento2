<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\GetTaxResult;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartInterface;

// TODO: Rename this class to something more semantically appropriate like TaxDetailsItemConverter or TaxDetailsItem/Get
class TaxDetailsItem
{
    /**
     * Prefix for applied taxes ID
     */
    const APPLIED_TAXES_ID_PREFIX = 'avatax-';

    /**
     * Stores the mapping association between the arbitrary codes used for gw_item and the quotes
     * associated with them. Refer to the two @see methods below for how core Magento sets and uses this array
     *
     * @see Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::_collectWrappingForItems()
     * @see Magento\GiftWrapping\Model\Total\Quote\Tax\GiftwrappingAfterTax::processWrappingForItems()
     * @var array
     */
    protected $gwItemCodeToItemMapping = [];

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
     * TaxDetailsItem constructor
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     */
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
     * Add quote item to gift wrap mapping array
     *
     * @param string $code
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return void
     */
    protected function addGwItemCodeMapping($code, \Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $this->gwItemCodeToItemMapping[$code] = $item;
    }

    /**
     * Convert a quote/order/invoice/credit memo item to a tax details item objects
     *
     * This includes tax for the item as well as any additional line item tax information like Gift Wrapping
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function getTaxDetailsItemForItem(CartItemInterface $item, GetTaxResult $getTaxResult, $useBaseCurrency)
    {
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
     * Convert QuoteItem to TaxDetailsItem for tax for item
     *
     * @param CartItemInterface $item
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function convertQuoteItemToTaxDetailsItem(
        CartItemInterface $item,
        GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        $taxLineNo = $item->getId();
        $taxDetailsItemCode = $item->getTaxCalculationItemId();
        $taxDetailsItemType = CommonTaxCollector::ITEM_TYPE_PRODUCT;
        $taxDetailsAssociatedItemCode = $item->getAssociatedItemCode();

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
        $price = $this->priceCurrency->round($price, $item->getQuote()->getStore());

        $quantity = $item->getQty(); // TODO: Add support for getting QTY from parent products See \Magento\Tax\Model\TaxCalculation::getTotalQuantity
        // TODO: Determine if we need to round $rowTotal
        // TODO: Switch to logic from \ClassyLlama\AvaTax\Framework\Interaction\Line::convertQuoteItemToData
        $rowTotal = $price * $quantity;

        $taxDetailsItem = $this->getTaxDetailsItem(
            $getTaxResult,
            $item->getQuote()->getQuoteId(),
            $taxLineNo,
            $price,
            $useBaseCurrency,
            $taxDetailsItemCode,
            $taxDetailsItemType,
            $taxDetailsAssociatedItemCode,
            $rowTotal
        );

        // If an item has children whose tax get calculated, then this item should not contain any Applied Taxes or else
        // the total of Applied Taxes will include both the parent product tax as well as all of the children taxes
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $taxDetailsItem->setAppliedTaxes([]);
        }

        return $taxDetailsItem;
    }

    /**
     * Convert QuoteItem to TaxDetailsItem for gift wrapping for item
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param GetTaxResult $getTaxResult
     * @param bool $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function convertQuoteItemToGiftWrapTaxDetailsItem(
        CartItemInterface $item,
        GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        $taxLineNo = Giftwrapping::CODE_ITEM_GW_PREFIX . $item->getId();
        $taxDetailsItemCode = $taxLineNo;
        $taxDetailsItemType = Giftwrapping::CODE_ITEM_GW_PREFIX;
        $taxDetailsAssociatedItemCode = $item->getTaxCalculationItemId();

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $item->getGwBasePrice();
        } else {
            $price = $item->getGwPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }

        $quantity = $item->getQty(); // TODO: Add support for getting QTY from parent products See \Magento\Tax\Model\TaxCalculation::getTotalQuantity
        // TODO: Determine if we need to round $rowTotal
        // TODO: Figure out what to do with this
        $rowTotal = $price * $quantity;

        return $this->getTaxDetailsItem(
            $getTaxResult,
            $item->getQuote()->getQuoteId(),
            $taxLineNo,
            $price,
            $useBaseCurrency,
            $taxDetailsItemCode,
            $taxDetailsItemType,
            $taxDetailsAssociatedItemCode,
            $rowTotal
        );
    }

    /**
     * Get all quote-level taxes and return them
     *
     * @param CartInterface $quote
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[]
     */
    public function getTaxDetailsItemsForQuote(CartInterface $quote, GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        $taxDetailsItems = [];

        $gwQuoteTaxDetailsItem = $this->convertQuoteToGwQuoteTaxDetailsItem($quote, $getTaxResult, $useBaseCurrency);
        if ($gwQuoteTaxDetailsItem) {
            $taxDetailsItems[] = $gwQuoteTaxDetailsItem;
        }

        $gwCardTaxDetailsItem = $this->convertQuoteToGwCardTaxDetailsItem($quote, $getTaxResult, $useBaseCurrency);
        if ($gwCardTaxDetailsItem) {
            $taxDetailsItems[] = $gwCardTaxDetailsItem;
        }

        $shippingTaxDetailsItem = $this->convertQuoteToShippingTaxDetailsItem($quote, $getTaxResult, $useBaseCurrency);
        if ($shippingTaxDetailsItem) {
            $taxDetailsItems[] = $shippingTaxDetailsItem;
        }

        return $taxDetailsItems;
    }

    /**
     * Convert Quote to TaxDetailsItem for shipping for quote
     *
     * @param CartInterface $quote
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function convertQuoteToShippingTaxDetailsItem(CartInterface $quote, GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        $taxLineNo = Line::SHIPPING_LINE_NO;
        $taxDetailsItemCode = CommonTaxCollector::ITEM_CODE_SHIPPING;
        $taxDetailsItemType = CommonTaxCollector::ITEM_TYPE_SHIPPING;
        $taxDetailsAssociatedItemCode = null; // TODO: Figure out what should be set here

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $quote->getShippingAddress()->getBaseShippingAmount(); // TODO: Should this have a price?
        } else {
            $price = $quote->getShippingAddress()->getShippingAmount(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }

        return $this->getTaxDetailsItem(
            $getTaxResult,
            $quote->getStoreId(),
            $taxLineNo,
            $price,
            $useBaseCurrency,
            $taxDetailsItemCode,
            $taxDetailsItemType,
            $taxDetailsAssociatedItemCode
        );
    }

    /**
     * Convert Quote to TaxDetailsItem for Gift Wrap for quote
     *
     * @param CartInterface $quote
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function convertQuoteToGwQuoteTaxDetailsItem(CartInterface $quote, GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        $taxLineNo = Giftwrapping::CODE_QUOTE_GW;
        $taxDetailsItemCode = Giftwrapping::CODE_QUOTE_GW;
        $taxDetailsItemType = Giftwrapping::QUOTE_TYPE;
        $taxDetailsAssociatedItemCode = CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE;

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $quote->getShippingAddress()->getGwBasePrice(); // TODO: Should this have a price?
        } else {
            $price = $quote->getShippingAddress()->getGwPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }

        return $this->getTaxDetailsItem(
            $getTaxResult,
            $quote->getStoreId(),
            $taxLineNo,
            $price,
            $useBaseCurrency,
            $taxDetailsItemCode,
            $taxDetailsItemType,
            $taxDetailsAssociatedItemCode
        );
    }

    /**
     * Convert Quote to TaxDetailsItem for Gift Wrap Card for quote
     *
     * @param CartInterface $quote
     * @param GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function convertQuoteToGwCardTaxDetailsItem(
        CartInterface $quote,
        GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        $taxLineNo = Giftwrapping::CODE_PRINTED_CARD;
        $taxDetailsItemCode = Giftwrapping::CODE_PRINTED_CARD;
        $taxDetailsItemType = Giftwrapping::PRINTED_CARD_TYPE;
        $taxDetailsAssociatedItemCode = CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE;

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            $price = $quote->getShippingAddress()->getGwCardBasePrice();
        } else {
            $price = $quote->getShippingAddress()->getGwCardPrice();
        }

        return $this->getTaxDetailsItem(
            $getTaxResult,
            $quote->getStoreId(),
            $taxLineNo,
            $price,
            $useBaseCurrency,
            $taxDetailsItemCode,
            $taxDetailsItemType,
            $taxDetailsAssociatedItemCode
        );
    }

    /**
     * Get TaxDetailsItem object based on passed data
     *
     * @param GetTaxResult $getTaxResult
     * @param $store
     * @param $taxLineNo
     * @param $price
     * @param $useBaseCurrency
     * @param $taxDetailsItemCode
     * @param $taxDetailsItemType
     * @param $taxDetailsAssociatedItemCode
     * @param null $rowTotal
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|bool
     */
    protected function getTaxDetailsItem(
        GetTaxResult $getTaxResult,
        $store,
        $taxLineNo,
        $price,
        $useBaseCurrency,
        $taxDetailsItemCode,
        $taxDetailsItemType,
        $taxDetailsAssociatedItemCode,
        $rowTotal = null
    ) {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine($taxLineNo);

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)($taxLine->getRate() * Tax::RATE_MULTIPLIER);
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this

        if (!$rowTotal) {
            // TODO: Determine if we need to round $rowTotal
            $rowTotal = $price;
        }

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax, $store);
        }

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;

        $appliedTaxes = $this->getAppliedTaxes($getTaxResult, $rowTax);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($taxDetailsItemCode)
            ->setType($taxDetailsItemType)
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($taxDetailsAssociatedItemCode)
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
