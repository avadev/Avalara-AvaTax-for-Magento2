<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api\Data;

/**
 * @api
 */
interface GrandTotalDetailsInterface extends \Magento\Tax\Api\Data\GrandTotalDetailsInterface
{
    // BEGIN EDIT - Update return types to display tax summary information on frontend
    // See https://github.com/classyllama/ClassyLlama_AvaTax/issues/70 for details
    /**
     * Applied tax rates info
     *
     * @return \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesInterface[]
     */
    public function getRates();

    /**
     * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesInterface[] $rates
     * @return $this
     */
    public function setRates($rates);
    // END EDIT
}
