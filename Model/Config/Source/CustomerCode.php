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
        $attributesArray[Config::CUSTOMER_FORMAT_OPTION_ID] = [
            'value' => Config::CUSTOMER_FORMAT_OPTION_ID, 'label' => __('ID')
        ];
        $attributesArray[Config::CUSTOMER_FORMAT_OPTION_EMAIL] = [
            'value' => Config::CUSTOMER_FORMAT_OPTION_EMAIL, 'label' => __('Email')
        ];
        $attributesArray[Config::CUSTOMER_FORMAT_OPTION_NAME_ID] = [
            'value' => Config::CUSTOMER_FORMAT_OPTION_NAME_ID, 'label' => __('Name (ID)')
        ];

        // Retrieve all customer attributes
        $customerAttributes = $this->customer->getAttributes();
        foreach ($customerAttributes as $attribute){
            $label = $attribute->getDefaultFrontendLabel();
            if (!is_null($label) && $attribute->getIsUserDefined()) {
                // Only add custom attribute codes that have a frontend label value defined
                $attributesArray[$attribute->getAttributeCode()] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => __($label)
                ];
            }
        }

        // Sort alphabetically for ease of use
        sort($attributesArray);

        return $attributesArray;
    }
}
