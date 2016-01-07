<?php

namespace ClassyLlama\AvaTax\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Source model for AvaTax Customer Usage Type
 */
class AvaTaxCustomerUsageType implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => ' '],
            ['value' => 'A', 'label' => __('Federal Government')],
            ['value' => 'B', 'label' => __('State/Local Govt.')],
            ['value' => 'C', 'label' => __('Tribal Government')],
            ['value' => 'D', 'label' => __('Foreign Diplomat')],
            ['value' => 'E', 'label' => __('Charitable Organization')],
            ['value' => 'F', 'label' => __('Religious/Education')],
            ['value' => 'G', 'label' => __('Resale')],
            ['value' => 'H', 'label' => __('Agricultural Production')],
            ['value' => 'I', 'label' => __('Industrial Prod/Mfg.')],
            ['value' => 'J', 'label' => __('Direct Pay Permit')],
            ['value' => 'K', 'label' => __('Direct Mail')],
            ['value' => 'L', 'label' => __('Other')],
            ['value' => 'N', 'label' => __('Local Government')],
            ['value' => 'P', 'label' => __('Commercial Aquaculture (Canada)')],
            ['value' => 'Q', 'label' => __('Commercial Fishery (Canada)')],
            ['value' => 'R', 'label' => __('Non-resident (Canada)')],
            ['value' => 'MED1', 'label' => __('US MDET with exempt sales tax')],
            ['value' => 'MED2', 'label' => __('US MDET with taxable sales tax')],
        ];
    }
}
