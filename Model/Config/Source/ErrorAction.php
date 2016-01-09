<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

use ClassyLlama\AvaTax\Model\Config;

class ErrorAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Config::ERROR_ACTION_DISABLE_CHECKOUT, 'label' => __('Disable checkout & show error message')],
            ['value' => Config::ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX, 'label' => __('Allow checkout & fall back to native Magento tax calculation (no error message)')],
        ];
    }
}
