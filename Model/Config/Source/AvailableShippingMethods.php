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

namespace ClassyLlama\AvaTax\Model\Config\Source;

class AvailableShippingMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $carrierMethodsProviders;

    /**
     * @param CarrierShippingMethodsProvider[] $carrierMethodsProviders
     */
    public function __construct(array $carrierMethodsProviders = [])
    {
        $this->carrierMethodsProviders = $carrierMethodsProviders;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array_filter(
            array_map(
                function ($carrierProvider) {
                    /** @var CarrierShippingMethodsProvider $carrierProvider */
                    return $carrierProvider->getCarrierOptions();
                },
                $this->carrierMethodsProviders
            )
        );
    }
}
