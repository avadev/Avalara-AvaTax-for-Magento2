<?php

use Magento\Tax\Model\Calculation;
use ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\SetupUtil;

$taxRate = 0.06;
$taxAmount = 400 * $taxRate;

$taxCalculationData['simple_product'] = [
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
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'simple1',
                'price' => 10,
                'qty' => 10,
            ],
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
                'price_type' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC,
                'sku' => 'bundle1',
                'price' => 10, // Price doesn't matter if price_type is dynamic
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
                            'bundle1_child1',
                            'bundle1_child2',
                            'bundle1_child3',
                        ]
                    ],
                ],
                // Children items don't have quantity specified, as quantity is determined by the 'bundled_options' array
                'children' => [
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child1',
                        'price' => 5,
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child2',
                        'price' => 10,
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle1_child3',
                        'price' => 15,
                    ],
                ],
            ],
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
                'price_type' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
                'sku' => 'bundle2',
                'price' => 10,
                'qty' => 5,
                'children' => [
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle2_child1',
                        'price' => 10, // Price doesn't matter if price_type is fixed
                        'qty' => 10, // Qty doesn't matter if price_type is fixed
                    ],
                    [
                        'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                        'sku' => 'bundle2_child2',
                        'price' => 10, // Price doesn't matter if price_type is fixed
                        'qty' => 10, // Qty doesn't matter if price_type is fixed
                    ],
                ],
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
                            'bundle2_child1',
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
                            'bundle2_child2',
                        ]
                    ],
                ],
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 400,
            'base_subtotal' => 400,
            'subtotal_incl_tax' => 400 + $taxAmount,
            'base_subtotal_incl_tax' => 400 + $taxAmount,
            'tax_amount' => $taxAmount,
            'base_tax_amount' => $taxAmount,
            'shipping_amount' => 0,
            'base_shipping_amount' => 0,
            'shipping_incl_tax' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_taxable' => 0,
            'base_shipping_taxable' => 0,
            'shipping_tax_amount' => 0,
            'base_shipping_tax_amount' => 0,
            'discount_amount' => 0,
            'base_discount_amount' => 0,
            'discount_tax_compensation_amount' => 0,
            'base_discount_tax_compensation_amount' => 0,
            'shipping_discount_tax_compensation_amount' => 0,
            'base_shipping_discount_tax_compensation_amount' => 0,
            'grand_total' => 20 + $taxAmount,
            'base_grand_total' => 20 + $taxAmount,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle1_child3-1-bundle1_child1-bundle1_child1-bundle1_child2-bundle1_child3' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle1_child1' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle1_child2' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle1_child3' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle2_child2-1-bundle2_child1-bundle2_child2' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 6.0,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10 + 0.6,
                'base_price_incl_tax' => 10 + 0.6,
                'row_total_incl_tax' => 20 + $taxAmount,
                'base_row_total_incl_tax' => 20 + $taxAmount,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'bundle2_child1' => [
                'tax_amount' => 0,
                'base_tax_amount' => 0,
            ],
            'bundle2_child2' => [
                'tax_amount' => 0,
                'base_tax_amount' => 0,
            ],
        ],
    ],
];
