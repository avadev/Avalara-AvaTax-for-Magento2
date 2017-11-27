<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api\Data;

/**
 * @api
 */
interface GrandTotalRatesInterface extends \Magento\Tax\Api\Data\GrandTotalRatesInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    );
}
