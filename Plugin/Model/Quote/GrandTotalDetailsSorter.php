<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */
namespace ClassyLlama\AvaTax\Plugin\Model\Quote;

class GrandTotalDetailsSorter
{

    public function afterProcess(\Magento\Quote\Model\Cart\TotalsConverter $subject, $totalSegments)
    {
        $taxExtensionAttributes = $totalSegments['tax']->getExtensionAttributes();
        $taxGrandtotalDetails = $taxExtensionAttributes->getTaxGrandtotalDetails();

        foreach($taxGrandtotalDetails as $taxGrandtotalDetail) {
            $rates = $taxGrandtotalDetail->getRates();
            usort($rates, function ($leftRate, $rightRate){
                if($leftRate === null) {
                    return -1;
                }

                if($rightRate === null) {
                    return 1;
                }

                return $leftRate < $rightRate ? 1 : -1;
            });

            $taxGrandtotalDetail->setRates($rates);
        }

        $taxExtensionAttributes->setTaxGrandtotalDetails($taxGrandtotalDetails);
        $totalSegments['tax']->setExtensionAttributes($taxExtensionAttributes);

        return $totalSegments;
    }
}