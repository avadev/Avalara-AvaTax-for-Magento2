<?php
/**
 * GiftwrappingPlugin.php
 *
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Erik Hansen <erik@classyllama.com>
 * @copyright   Copyright (c) 2015 Erik Hansen & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\GiftWrapping\Total\Quote\Tax;

use \ClassyLlama\AvaTax\Model\Config;

class GiftwrappingPlugin
{
    /**
     * @var Config
     */
    protected $config = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Don't run native Magento code if AvaTax module is enabled, as Gift Wrapping tax calculation is handled in
     * ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax::collect
     *
     * @param \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping
     */
    public function aroundCollect(
        \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if ($this->config->isModuleEnabled($quote->getStore())) {
            return $this;
        }

        return $proceed($quote, $shippingAssignment, $total);
    }
}
