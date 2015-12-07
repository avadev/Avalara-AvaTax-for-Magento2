<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\AvaTax\Model\Config\Source;

class AddressValidationCountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Countries
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_countryCollection;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct(\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection)
    {
        $this->_countryCollection = $countryCollection;
    }

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
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
