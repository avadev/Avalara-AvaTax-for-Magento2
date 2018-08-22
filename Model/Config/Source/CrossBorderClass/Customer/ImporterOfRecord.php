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

namespace ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Customer;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use ClassyLlama\AvaTax\Helper\CustomsConfig;

/**
 * Class ImporterOfRecord
 * @package ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Customer
 *
 * Source model for form select field
 */
class ImporterOfRecord extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {

        if (!$this->_options) {
                $this->_options = [

                    ['label' => __('Use Default'), 'value' => CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_DEFAULT],
                    ['label' => __('Override to Yes'), 'value' => CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_YES],
                    ['label' => __('Override to No'), 'value' => CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_NO]

                ];
        }
        return $this->_options;
    }
}