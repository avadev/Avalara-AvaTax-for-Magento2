<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Quote;

class GrandTotalDetailsPlugin extends GrandTotalDetailsPluginAbstract
{
    /**
     * Define constant for tax amount array key
     */
    const KEY_TAX = 'tax';

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
