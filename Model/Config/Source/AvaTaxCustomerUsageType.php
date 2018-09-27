<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

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
            ['value' => 'A', 'label' => __('A - Federal Government')],
            ['value' => 'B', 'label' => __('B - State/Local Govt.')],
            ['value' => 'C', 'label' => __('C - Tribal Government')],
            ['value' => 'D', 'label' => __('D - Foreign Diplomat')],
            ['value' => 'E', 'label' => __('E - Charitable Organization')],
            ['value' => 'F', 'label' => __('F - Religious Organization')],
            ['value' => 'G', 'label' => __('G - Resale')],
            ['value' => 'H', 'label' => __('H - Agricultural Production')],
            ['value' => 'I', 'label' => __('I - Industrial Prod/Mfg.')],
            ['value' => 'J', 'label' => __('J - Direct Pay Permit')],
            ['value' => 'K', 'label' => __('K - Direct Mail')],
            ['value' => 'L', 'label' => __('L - Other')],
            ['value' => 'M', 'label' => __('M - Education Organization')],
            ['value' => 'N', 'label' => __('N - Local Government')],
            ['value' => 'P', 'label' => __('P - Commercial Aquaculture (Canada)')],
            ['value' => 'Q', 'label' => __('Q - Commercial Fishery (Canada)')],
            ['value' => 'R', 'label' => __('R - Non-resident (Canada)')],
            ['value' => 'MED1', 'label' => __('MED1 - US MDET with exempt sales tax')],
            ['value' => 'MED2', 'label' => __('MED2 - US MDET with taxable sales tax')],
        ];
    }
}
