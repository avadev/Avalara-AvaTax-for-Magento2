<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */
namespace ClassyLlama\AvaTax\Plugin\Model\Quote;

class GrandTotalDetailsSorter
{
    const CUSTOMS_RATE_TITLE = 'Duty';

    /**
     * Sorts the total rates by percentage ascending and sorts order of taxes by title
     *
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param                                           $totalSegments
     *
     * @return mixed
     */
    public function afterProcess(\Magento\Quote\Model\Cart\TotalsConverter $subject, $totalSegments)
    {
        $taxExtensionAttributes = $totalSegments['tax']->getExtensionAttributes();
        $taxGrandtotalDetails = $taxExtensionAttributes->getTaxGrandtotalDetails();

        foreach($taxGrandtotalDetails as $taxGrandtotalDetail) {
            $rates = $taxGrandtotalDetail->getRates();

            /** Sorts the total rates by percentage ascending */
            usort($rates, function ($leftRate, $rightRate){
                $leftPercent = $leftRate->getPercent();
                $rightPercent = $rightRate->getPercent();

                $leftRateIsCustoms = $leftRate->getTitle() === self::CUSTOMS_RATE_TITLE;
                $rightRateIsCustoms = $rightRate->getTitle() === self::CUSTOMS_RATE_TITLE;

                if($leftPercent === $rightPercent || ($leftRateIsCustoms && $rightRateIsCustoms)) {
                    return 0;
                }

                if($leftPercent === null || $leftRateIsCustoms) {
                    return 1;
                }

                if($rightPercent === null || $rightRateIsCustoms) {
                    return -1;
                }

                return $leftPercent < $rightPercent ? 1 : -1;
            });

            $taxGrandtotalDetail->setRates($rates);
        }

        /** Sorts order of taxes by title */
        usort($taxGrandtotalDetails, function ($leftTax, $rightTax){

            $leftTitle = $leftTax->getRates()[0]->getTitle() === self::CUSTOMS_RATE_TITLE;
            $rightTitle = $rightTax->getRates()[0]->getTitle() === self::CUSTOMS_RATE_TITLE;

            if($leftTitle && $rightTitle) {
                return 0;
            }

            if($rightTitle) {
                return 1;
            }

            return -1;
        });

        $taxExtensionAttributes->setTaxGrandtotalDetails($taxGrandtotalDetails);
        $totalSegments['tax']->setExtensionAttributes($taxExtensionAttributes);

        return $totalSegments;
    }
}