<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Quote;

class GrandTotalDetailsPlugin extends \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
{
    // BEGIN EDIT - Add extension factory
    /**
     * Define constant for tax amount array key
     */
    const KEY_TAX = 'tax';

    /**
     * @var \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory
     */
    protected $grandTotalRatesExtensionFactory;

    /**
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
     * @param \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
     */
    public function __construct(
        \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
        \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
    ) {
        $this->grandTotalRatesExtensionFactory = $grandTotalRatesExtensionFactory;
        parent::__construct(
        $detailsFactory,
        $ratesFactory,
        $totalSegmentExtensionFactory,
        $taxConfig
        );
    }
    // END EDIT

    /**
     * @param array $rates
     * @return array
     */
    protected function getRatesData($rates)
    {
        $taxRates = [];
        foreach ($rates as $rate) {
            $taxRate = $this->ratesFactory->create([]);
            $taxRate->setPercent($rate['percent']);
            $taxRate->setTitle($rate['title']);
            // BEGIN EDIT - Add extension attributes element to array with tax amount
            $extensionAttributes = $this->getExtensionAttributesArray($rate);
            if ($extensionAttributes) {
                $taxRateExtension = $this->grandTotalRatesExtensionFactory->create();
                $taxRateExtension->setTax($rate['extension_attributes']['tax']);
                $taxRate->setExtensionAttributes($taxRateExtension);
            }
            // END EDIT
            $taxRates[] = $taxRate;
        }
        return $taxRates;
    }

    /**
     * @param $rate
     * @return bool
     */
    protected function getExtensionAttributesArray($rate)
    {
        if (isset($rate[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][self::KEY_TAX])) {
            return $rate[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][self::KEY_TAX];
        }
        return false;
    }
}
