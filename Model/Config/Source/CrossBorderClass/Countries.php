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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass;

class Countries extends \Magento\Directory\Model\Config\Source\Country
{
    const OPTION_VAL_ANY = '0';

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = true, $foregroundCountries = '')
    {
        $results = parent::toOptionArray(true, \ClassyLlama\AvaTax\Helper\Config::$taxCalculationCountriesDefault);

        // Add "Any" option
        array_unshift($results, ['value' => self::OPTION_VAL_ANY, 'label' => __('-- Any Country --'), 'is_region_visible' => true]);

        return $results;
    }
}
