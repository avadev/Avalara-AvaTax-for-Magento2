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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Config\Source\CarrierMethodProviders;

class UPS implements \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface
{
    /**
     * @var \Magento\Ups\Helper\Config
     */
    protected $carrier;

    /**
     * @param \Magento\Ups\Helper\Config $carrier
     */
    public function __construct(\Magento\Ups\Helper\Config $carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * @return array
     */
    public function getCarrierMethods()
    {
        return $this->carrier->getCode('method');
    }
}