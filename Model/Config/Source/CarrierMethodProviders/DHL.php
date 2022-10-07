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

use Magento\Framework\Exception\LocalizedException;

class DHL implements \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface
{
    /**
     * @var \Magento\Dhl\Model\Carrier
     */
    protected $carrier;

    /**
     * @param \Magento\Dhl\Model\Carrier $carrier
     */
    public function __construct(\Magento\Dhl\Model\Carrier $carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * @return array
     */
    public function getCarrierMethods()
    {
        return $this->carrier->getDhlProducts($this->carrier->getConfigData('content_type'));
    }

    /**
     * @return array
     */
    public function getConfiguredMethods()
    {
        try {
            return array_keys($this->carrier->getAllowedMethods());
        } catch (\Exception $e) {
            return [];
        }
    }
}