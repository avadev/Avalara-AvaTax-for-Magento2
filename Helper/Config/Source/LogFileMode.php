<?php

namespace ClassyLlama\AvaTax\Helper\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LogFileMode implements ArrayInterface
{
    const COMBINED = 1;
    const SEPARATE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Combined With Magento Log Files')],
            ['value' => 2, 'label' => __('Separate AvaTax Log File')],
        ];
    }
}
