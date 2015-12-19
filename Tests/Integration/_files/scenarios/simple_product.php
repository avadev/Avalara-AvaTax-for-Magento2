<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Calculation;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\SetupUtil;

$taxRate = 0.06;
$taxAmount = 20 * $taxRate;

$taxCalculationData['simple_product'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => $credentialsConfig,
//        SetupUtil::TAX_RATE_OVERRIDES => [
//            SetupUtil::TAX_RATE_TX => 8.25,
//        ],
//        SetupUtil::TAX_RULE_OVERRIDES => [
//        ],
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
        'items' => [
            [
                'sku' => 'simple1',
                'price' => 10,
                'qty' => 2,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 20,
            'base_subtotal' => 20,
            'subtotal_incl_tax' => 20 + $taxAmount,
            'base_subtotal_incl_tax' => 20 + $taxAmount,
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
        ],
    ],
];
