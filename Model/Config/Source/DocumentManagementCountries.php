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

namespace ClassyLlama\AvaTax\Model\Config\Source;

class DocumentManagementCountries extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * {@inheritDoc}
     */
    public function toOptionArray($isMultiselect = true, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->addCountryCodeFilter(
                \ClassyLlama\AvaTax\Helper\Config::DOCUMENT_MANAGEMENT_COUNTRIES_DEFAULT
            )->loadData()->setForegroundCountries(
                \ClassyLlama\AvaTax\Helper\Config::DOCUMENT_MANAGEMENT_COUNTRIES_DEFAULT
            )->toOptionArray(
                false
            );
        }

        // Make US and CA show at top of list
        return parent::toOptionArray($isMultiselect, $foregroundCountries);
    }
}
