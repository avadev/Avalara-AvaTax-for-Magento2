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

namespace ClassyLlama\AvaTax\Model\Config\Source;

class AddressValidationCountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Creates list of countries to enable for address validation
     *
     * Currently only canada and the us are supported by AvaTax address validation so those are the only two countries
     * currently in the option array. More countries should be added to this array when AvaTax supports more countries
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'US', 'label' => __('United States')],
            ['value' => 'CA', 'label' => __('Canada')]
        ];

        return $options;
    }
}
