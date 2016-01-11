<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Plugin\Tax\Model\Sales\Total\Quote;

use ClassyLlama\AvaTax\Model\Config;

class Shipping
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Class constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * If module is enabled, don't run collect totals for shipping
     *
     * Tax calculation for shipping is handled in this class
     * @see \ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax::collect()
     * Since this extension doesn't support applying discounts *after* tax, we don't need to run a separate collect
     * process.
     *
     * @param \Magento\Tax\Model\Sales\Total\Quote\Shipping $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return mixed
     */
    public function aroundCollect(
        \Magento\Tax\Model\Sales\Total\Quote\Shipping $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $storeId = $quote->getStoreId();
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
        ) {
            return $proceed($quote, $shippingAssignment, $total);
        }
    }
}
