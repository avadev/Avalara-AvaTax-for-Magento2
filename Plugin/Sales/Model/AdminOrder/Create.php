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

namespace ClassyLlama\AvaTax\Plugin\Sales\Model\AdminOrder;

use ClassyLlama\AvaTax\Helper\Config;
use \Closure;
use Magento\Sales\Model\AdminOrder\Create as SalesCreate;

/**
 * Class Create
 */
class Create
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Create constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * If module is enabled, totals are not collected for subtotal
     *
     * When shipping is set as billing, base subtotal incl tax which will be used while collecting carrier rates
     * should be explicitly set to shipping address
     *
     * @param SalesCreate $subject
     * @param Closure $proceed
     * @param bool $flag
     * @return SalesCreate $result
     */
    public function aroundSetShippingAsBilling(SalesCreate $subject, Closure $proceed, $flag)
    {
        $canBeUpdated = $this->canBeUpdated($subject);
        $address = $subject->getShippingAddress();
        $baseSubtotalInclTax = $address->getBaseSubtotalTotalInclTax();
        $result = $proceed($flag);

        if ($canBeUpdated && $flag) {
            $address->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
        }

        return $result;
    }

    /**
     * Check whether shipping address update can be applied
     *
     * @param SalesCreate $subject
     * @return bool
     */
    private function canBeUpdated(SalesCreate $subject)
    {
        $storeId = $subject->getSession()->getStoreId();
        $address = $subject->getShippingAddress();

        return $this->config->isModuleEnabled($storeId)
            && $this->config->getTaxMode($storeId) != Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            && $this->config->isAddressTaxable($address, $storeId);
    }
}
