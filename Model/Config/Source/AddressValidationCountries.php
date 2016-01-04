<?php

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
