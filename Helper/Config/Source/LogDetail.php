<?php

namespace ClassyLlama\AvaTax\Helper\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LogDetail implements ArrayInterface
{
    const MINIMAL = 1;
    const NORMAL = 2;
    const EXTRA = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MINIMAL, 'label' => __('Minimal')],
            ['value' => self::NORMAL, 'label' => __('Normal')],
            ['value' => self::EXTRA, 'label' => __('Extra')],
        ];
    }
}
