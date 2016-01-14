<?php

namespace ClassyLlama\AvaTax\Helper\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Production')], ['value' => 0, 'label' => __('Development')]];
    }
}
