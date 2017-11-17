<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Plugin\Sales\Total\Quote;

class CommonTaxCollector
{
    /**
     * @param \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject
     * @param \Closure $proceed
     * @param $appliedTaxes
     * @param $baseAppliedTaxes
     * @param array $extraInfo
     * @return array
     */
    public function aroundConvertAppliedTaxes(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject,
        \Closure $proceed,
        $appliedTaxes,
        $baseAppliedTaxes,
        $extraInfo = []
    ) {
        $appliedTaxesArray = [];

        if (!$appliedTaxes || !$baseAppliedTaxes) {
            return $appliedTaxesArray;
        }

        foreach ($appliedTaxes as $taxId => $appliedTax) {
            $baseAppliedTax = $baseAppliedTaxes[$taxId];
            $rateDataObjects = $appliedTax->getRates();

            $rates = [];
            foreach ($rateDataObjects as $rateDataObject) {
                $rates[] = [
                    'percent' => $rateDataObject->getPercent(),
                    'code' => $rateDataObject->getCode(),
                    'title' => $rateDataObject->getTitle(),
                    // BEGIN EDIT - Add extension attributes array to rates array
                    'extension_attributes' => ['tax' => $rateDataObject->getExtensionAttributes()->getTax()]
                    // END EDIT
                ];
            }

            $appliedTaxArray = [
                'amount' => $appliedTax->getAmount(),
                'base_amount' => $baseAppliedTax->getAmount(),
                'percent' => $appliedTax->getPercent(),
                'id' => $appliedTax->getTaxRateKey(),
                'rates' => $rates,
            ];
            if (!empty($extraInfo)) {
                $appliedTaxArray = array_merge($appliedTaxArray, $extraInfo);
            }

            $appliedTaxesArray[] = $appliedTaxArray;
        }

        return $appliedTaxesArray;
    }
}
