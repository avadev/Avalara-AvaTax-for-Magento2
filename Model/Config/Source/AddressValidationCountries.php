<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

class AddressValidationCountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Currently only canada and the us are supported by AvaTax address validation so those are the only two countries
     * currently in the option array. More countries should be added to this array when AvaTax supports more countries
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'CA', 'label' => __('Canada')],
            ['value' => 'US', 'label' => __('United States')]
        ];

        return $options;
    }
}
