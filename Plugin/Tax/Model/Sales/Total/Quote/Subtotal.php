<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Plugin\Tax\Model\Sales\Total\Quote;

use ClassyLlama\AvaTax\Helper\Config;

class Subtotal
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
     * If module is enabled, don't run collect totals for subtotal
     *
     * Tax calculation for shipping is handled in this class
     * @see \ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax::collect()
     * Since this extension doesn't support applying discounts or shipping to the post-tax amount, there is no need to
     * run this collect method.
     *
     * @param \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return mixed
     */
    public function aroundCollect(
        \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $storeId = $quote->getStoreId();
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $address = $shippingAssignment->getShipping()->getAddress();
        if (!$this->config->isModuleEnabled($storeId)
            || $this->config->getTaxMode($storeId) == Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            || !$this->config->isAddressTaxable($address, $storeId)
        ) {
            return $proceed($quote, $shippingAssignment, $total);
        }
    }
}
