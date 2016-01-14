<?php

namespace ClassyLlama\AvaTax\Helper\Config\Source;

use ClassyLlama\AvaTax\Helper\Config;

class CustomerCode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Config::CUSTOMER_FORMAT_OPTION_ID, 'label' => __('ID')],
            ['value' => Config::CUSTOMER_FORMAT_OPTION_EMAIL, 'label' => __('Email')],
            ['value' => Config::CUSTOMER_FORMAT_OPTION_NAME_ID, 'label' => __('Name (ID)')],
        ];
    }
}
