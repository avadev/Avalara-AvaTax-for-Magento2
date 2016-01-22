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

$taxCalculationData['bundled_product_fixed_pricing'] = [
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
        'shipping' => [
            'method' => 'flatrate_flatrate',
            'amount' => 6,
            'base_amount' => 6,
        ],
        'shopping_cart_rules' => [
            [
                'discount_amount' => 15,
            ],
        ],
        'currency_rates' => [
            'base_currency_code' => 'USD',
            'quote_currency_code' => 'EUR',
            'currency_conversion_rate' => 2,
        ],
        'items' => [
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
                'price_type' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
                'sku' => 'bundle1',
                // This price is arbitrary, but is based on the dynamic price in the bundled_product_dynamic_pricing.php file
                'price' => 380,
                'qty' => 3,
                'bundled_options' => [
                    [
                        'title' => 'Option 1',
                        'default_title' => 'Option 1',
                        'type' => 'select',
                        'required' => 1,
                        'delete' => '',
                        'qty' => 5,
                        // Only add multiple SKUs for multi/checkbox options
                        'selected_skus' => [
                            'bundle1_child1',
                        ]
                    ],
                    [
                        'title' => 'Option 2',
                        'default_title' => 'Option 2',
                        'type' => 'checkbox',
                        'required' => 1,
                        'delete' => '',
                        'qty' => 7,
                        // Only add multiple SKUs for multi/checkbox options
                        'selected_skus' => [
                            'bundle1_child2',
                            'bundle1_child3',
                        ]
                    ],
                    [
                        'title' => 'Option 3',
                        'default_title' => 'Option 3',
                        'type' => 'checkbox',
                        'required' => 1,
                        'delete' => '',
                        'qty' => 9,
                        // Only add multiple SKUs for multi/checkbox options
                        'selected_skus' => [
                            'bundle1_child4',
                        ]
                    ],
                ],
                // Children items don't have quantity specified, as quantity is determined by the 'bundled_options' array
                'children' => [
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child1',
                        'price' => 5, // Price doesn't affect anything since price_type is fixed
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child2',
                        'price' => 10, // Price doesn't affect anything since price_type is fixed
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child3',
                        'price' => 15, // Price doesn't affect anything since price_type is fixed
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child4',
                        'price' => 20, // Price doesn't affect anything since price_type is fixed
                    ],
                ],
            ],
        ],
    ],
    'expected_results' => [
        // There is an issue comparing with bundled products, so disabling comparison with native Magento
        'compare_with_native_tax_calculation' => false,
        'address_data' => [
            'subtotal' => 2280.0,
            'base_subtotal' => 1140.0,
            'subtotal_incl_tax' => 2416.80,
            'base_subtotal_incl_tax' => 1208.40,
            'tax_amount' => 118.08,
            'base_tax_amount' => 59.04,
            'shipping_amount' => 30.0,
            'base_shipping_amount' => 15,
            'shipping_incl_tax' => 31.80,
            'base_shipping_incl_tax' => 15.9,
            'shipping_taxable' => 0,
            'base_shipping_taxable' => 0,
            'shipping_tax_amount' => 1.8,
            'base_shipping_tax_amount' => 0.9,
            'discount_amount' => -342.0,
            'base_discount_amount' => -171.0,
            'discount_tax_compensation_amount' => 0,
            'base_discount_tax_compensation_amount' => 0,
            'shipping_discount_tax_compensation_amount' => 0,
            'base_shipping_discount_tax_compensation_amount' => 0,
            'grand_total' => 2086.08,
            'base_grand_total' => 1043.04,
        ],
        'items_data' => [
            'bundle1' => [
                'row_total' => 2280.0,
                'base_row_total' => 1140.0,
                'tax_percent' => 6.0,
                'price' => 380, // Price is not multiplied by conversion rate, which is same as native Magento
                'base_price' => 380.0,
                'price_incl_tax' => 805.60,
                'base_price_incl_tax' => 402.80,
                'row_total_incl_tax' => 2416.80,
                'base_row_total_incl_tax' => 1208.40,
                'tax_amount' => 116.28,
                'base_tax_amount' => 58.14,
                'discount_amount' => 342.0,
                'base_discount_amount' => 171.0,
                'discount_percent' => 15,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
                'qty' => 3,
            ],
            // Since the price is fixed, the parent item will contain the price information
            'bundle1_child1' => ['row_total' => null, 'qty' => 5],
            'bundle1_child2' => ['row_total' => null, 'qty' => 7],
            'bundle1_child3' => ['row_total' => null, 'qty' => 7],
            'bundle1_child4' => ['row_total' => null, 'qty' => 9],
        ],
    ],
];
