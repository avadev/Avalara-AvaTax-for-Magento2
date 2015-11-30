<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

class LogFileMode implements \Magento\Framework\Option\ArrayInterface
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
