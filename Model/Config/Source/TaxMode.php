<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

use ClassyLlama\AvaTax\Helper\Config;

class TaxMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT, 'label' => __('Disabled')],
            ['value' => Config::TAX_MODE_ESTIMATE_ONLY, 'label' => __('Estimate Tax')],
            ['value' => Config::TAX_MODE_ESTIMATE_AND_SUBMIT, 'label' => __('Estimate Tax & Submit Transactions to AvaTax (default)')],
        ];
    }
}
