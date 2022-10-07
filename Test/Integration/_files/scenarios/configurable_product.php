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

$taxCalculationData['configurable_product'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => $credentialsConfig,
    ],
    'quote_data' => [
        'billing_address' => [
            'region_id' => SetupUtil::REGION_MI,
        ],
        'shipping_address' => [
            'region_id' => SetupUtil::REGION_MI,
        ],
        'shopping_cart_rules' => [
            [
                'discount_amount' => 15,
            ],
        ],
        'items' => [
            [
                'type' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                'sku' => 'configurable1',
                'price' => 10,
                'qty' => 5,
                // Child products will be automatically created for each option. The first child product/option will be
                // selected and added to cart. The SKU naming pattern is <PARENT_SKU>_child<AUTO_INCREMENT_INDEX> where
                // AUTO_INCREMENT_INDEX is starting at one. e.g., configurable1_child1
                'options' => [
                    'value' => [
                        'option_0' => ['Option 1'],
                        'option_1' => ['Option 2']
                    ],
                    'order' => [
                        'option_0' => 1,
                        'option_1' => 2
                    ],
                ],
            ],
        ],
    ],
    'expected_results' => [
        'compare_with_native_tax_calculation' => true,
        'address_data' => [
            'subtotal' => 50,
            'subtotal_incl_tax' => 53,
            'tax_amount' => 2.55,
            'discount_amount' => -7.5,
            'discount_tax_compensation_amount' => 0,
            'shipping_discount_tax_compensation_amount' => 0,
            'grand_total' => 45.05,
        ],
        'items_data' => [
            'configurable1' => [
                'qty' => 5,
                'row_total' => 50,
                'tax_percent' => 6.0,
                'price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 53,
                'tax_amount' => 2.55,
                'discount_amount' => 7.5,
                'discount_percent' => 15,
                'discount_tax_compensation_amount' => 0,
            ],
            'configurable1_child1' => [
                'qty' => 1, // Simple children have a QTY of 1, regardless of parent quantity
                // Simple children don't have values calculated
                'row_total' => 0,
                'tax_percent' => 0,
                'price' => 0,
                'price_incl_tax' => 0,
                'row_total_incl_tax' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
