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

use Magento\Tax\Model\Calculation;
use ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\SetupUtil;

$tennesseeAddress = [
    'postcode' => '37243-9034',
    'country_id' => SetupUtil::COUNTRY_US,
    'city' => 'Nashville',
    'street' => ['600 Charlotte Ave'],
    'region_id' => 56, // Tennessee
];

/**
 * The purpose of this test is to verify the "$taxableAmountPercentage" functionality in
 * @see \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::getTaxDetailsItem
 */
$taxCalculationData['customer_tax_class'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => $credentialsConfig,
    ],
    'quote_data' => [
        'billing_address' => $tennesseeAddress,
        'shipping_address' => $tennesseeAddress,
        'shopping_cart_rules' => [
            [
                'discount_amount' => 20,
            ],
        ],
        'items' => [
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'simple1',
                'price' => 10,
                'qty' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'compare_with_native_tax_calculation' => false,
        'address_data' => [
            'subtotal' => 10,
            'subtotal_incl_tax' => 10.71, // If functionality is broken, it will return 10.76
            'tax_amount' => 0.56999999999999995,
            'grand_total' => 8.57,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10,
                'tax_percent' => 9.25,
                'price' => 10,
                'price_incl_tax' => 10.71, // If functionality is broken, it will return 10.76
                'row_total_incl_tax' => 10.71, // If functionality is broken, it will return 10.76
                'tax_amount' => 0.56999999999999995,

                'discount_amount' => 2,
                'tax_before_discount' => 0,
            ],
        ],
    ],
];
