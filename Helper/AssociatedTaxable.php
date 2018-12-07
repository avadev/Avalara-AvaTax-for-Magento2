<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class AssociatedTaxable extends AbstractHelper
{

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var TaxClass
     */
    protected $taxClassHelper;

    /**
     * @param Context  $context
     * @param Config   $configHelper
     * @param TaxClass $taxClassHelper
     */
    public function __construct(
        Context $context,
        \ClassyLlama\AvaTax\Helper\Config $configHelper,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->taxClassHelper = $taxClassHelper;
    }

    /**
     * @param array $associatedTaxableData
     *
     * @param       $storeId
     *
     * @return array
     */
    public function updateData($associatedTaxableData, $storeId)
    {
        $data = $associatedTaxableData;
        $type = $associatedTaxableData[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE];
        switch ($type) {
            case Giftwrapping::ITEM_TYPE:
                $data = $this->handleGiftWrappingItem($associatedTaxableData, $storeId);
                break;
            case Giftwrapping::QUOTE_TYPE:
                $data = $this->handleGiftWrappingQuote($data, $storeId);
                break;
            case Giftwrapping::PRINTED_CARD_TYPE:
                $data = $this->handlePrintedCard($data, $storeId);
                break;
            default:
                break;
        }
        return $data;
    }

    /**
     * @param $data
     * @param $storeId
     *
     * @return array
     */
    public function handleGiftWrappingItem($data, $storeId)
    {
        $itemCode = $this->configHelper->getSkuShippingGiftWrapItem($storeId);
        $taxClassId = $this->taxClassHelper->getAvataxTaxClassIdForGiftOptions($storeId);
        // TODO: Description?
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE] = $itemCode;
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID] = $taxClassId;
        return $data;
    }

    /**
     * @param $data
     * @param $storeId
     *
     * @return array
     */
    public function handleGiftWrappingQuote($data, $storeId)
    {
        $itemCode = $this->configHelper->getSkuGiftWrapOrder($storeId);
        $taxClassId = $this->taxClassHelper->getAvataxTaxClassIdForGiftOptions($storeId);
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE] = $itemCode;
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID] = $taxClassId;
        return $data;
    }

    /**
     * @param $data
     * @param $storeId
     *
     * @return array
     */
    public function handlePrintedCard($data, $storeId)
    {
        $itemCode = $this->configHelper->getSkuShippingGiftWrapCard($storeId);
        $taxClassId = $this->taxClassHelper->getAvataxTaxClassIdForGiftOptions($storeId);
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE] = $itemCode;
        $data[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID] = $taxClassId;
        return $data;
    }
}
