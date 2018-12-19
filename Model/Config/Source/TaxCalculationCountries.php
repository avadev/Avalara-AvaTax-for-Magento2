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

class TaxCalculationCountries extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = true, $foregroundCountries = '')
    {
        // Make US and CA show at top of list
        if (!$foregroundCountries) {
            $foregroundCountries = \ClassyLlama\AvaTax\Helper\Config::$taxCalculationCountriesDefault;
        }

        return parent::toOptionArray($isMultiselect, $foregroundCountries);
    }
}
