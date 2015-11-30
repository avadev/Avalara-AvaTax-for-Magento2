<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

class LogDetail implements \Magento\Framework\Option\ArrayInterface
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
