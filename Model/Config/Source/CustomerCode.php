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

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Customer\Model\Customer;

class CustomerCode implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Constants defined for default option labels
     */
    const CUSTOMER_FORMAT_OPTION_ID_LABEL = 'ID';
    const CUSTOMER_FORMAT_OPTION_NAME_ID_LABEL = 'Name (ID)';
    /**#@-*/

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * CustomerCode constructor.
     *
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        // Define attributes array with default config values to include
        $attributesArray[self::CUSTOMER_FORMAT_OPTION_ID_LABEL] = [
            'value' => Config::CUSTOMER_FORMAT_OPTION_ID, 'label' => __(self::CUSTOMER_FORMAT_OPTION_ID_LABEL)
        ];
        $attributesArray[self::CUSTOMER_FORMAT_OPTION_NAME_ID_LABEL] = [
            'value' => Config::CUSTOMER_FORMAT_OPTION_NAME_ID, 'label' => __(self::CUSTOMER_FORMAT_OPTION_NAME_ID_LABEL)
        ];

        // Retrieve all customer attributes
        $customerAttributes = $this->customer->getAttributes();
        foreach ($customerAttributes as $attribute){
            $label = $attribute->getDefaultFrontendLabel();
            if (!is_null($label)) {
                // Only add custom attribute codes that have a frontend label value defined
                if (isset($attributesArray[$label])) {
                    // An option already exists with this label, append the attribute code to this one for clarification
                    $label .= ' (' . $attribute->getAttributeCode() . ')';
                }
                $attributesArray[$label] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => __($label)
                ];
            }
        }

        // Sort alphabetically for ease of use
        ksort($attributesArray);

        return $attributesArray;
    }
}
