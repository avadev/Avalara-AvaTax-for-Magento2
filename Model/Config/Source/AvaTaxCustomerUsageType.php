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
            ['value' => 'A', 'label' => 'Federal Government'],
            ['value' => 'B', 'label' => 'State/Local Govt.'],
            ['value' => 'C', 'label' => 'Tribal Government'],
            ['value' => 'D', 'label' => 'Foreign Diplomat'],
            ['value' => 'E', 'label' => 'Charitable Organization'],
            ['value' => 'F', 'label' => 'Religious/Education'],
            ['value' => 'G', 'label' => 'Resale'],
            ['value' => 'H', 'label' => 'Agricultural Production'],
            ['value' => 'I', 'label' => 'Industrial Prod/Mfg.'],
            ['value' => 'J', 'label' => 'Direct Pay Permit'],
            ['value' => 'K', 'label' => 'Direct Mail'],
            ['value' => 'L', 'label' => 'Other'],
            ['value' => 'N', 'label' => 'Local Government'],
            ['value' => 'P', 'label' => 'Commercial Aquaculture (Canada)'],
            ['value' => 'Q', 'label' => 'Commercial Fishery (Canada)'],
            ['value' => 'R', 'label' => 'Non-resident (Canada)'],
            ['value' => 'MED1', 'label' => 'US MDET with exempt sales tax'],
            ['value' => 'MED2', 'label' => 'US MDET with taxable sales tax'],
        ];
    }
}
