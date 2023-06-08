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

class Fedex implements \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface
{
    /**
     * @var \Magento\Fedex\Model\Carrier
     */
    protected $carrier;

    /**
     * @param \Magento\Fedex\Model\Carrier $carrier
     */
    public function __construct(\Magento\Fedex\Model\Carrier $carrier)
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

    /**
     * @return array
     */
    public function getConfiguredMethods()
    {
        try {
            $allowedMethods = $this->carrier->getConfigData('allowed_methods');
            if ($allowedMethods) {
                return explode(",", (string)$allowedMethods);
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}