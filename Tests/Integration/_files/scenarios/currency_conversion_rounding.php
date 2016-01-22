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

/**
 * This test verifies the conversion rounding logic in
 * @see \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::getTaxDetailsItem
 */
$taxCalculationData['currency_conversion_rounding'] = [
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
        'currency_rates' => [
            'base_currency_code' => 'USD',
            'quote_currency_code' => 'EUR',
            'currency_conversion_rate' => 2,
        ],
        'items' => [
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'simple1',
                'price' => 9.90,
                'qty' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'compare_with_native_tax_calculation' => true,
        'address_data' => [
            'subtotal' => 19.8,
            'base_subtotal' => 9.9,
            'subtotal_incl_tax' => 19.8 + 1.19,
            'base_subtotal_incl_tax' => 9.9 + 0.59,
            'tax_amount' => 1.19,
            'base_tax_amount' => 0.59,
            'grand_total' => 19.8 + 1.19,
            'base_grand_total' => 9.9 + 0.59,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 19.8,
                'base_row_total' => 9.9,
                'tax_percent' => 6.0,
                'price' => 9.9, // It seems like this should be 19.8, but it's not. It's also 9.9 in native Magento.
                'base_price' => 9.9,
                'price_incl_tax' => 19.8 + 1.19, // 1.19 is 1.188 rounded up
                'base_price_incl_tax' => 9.9 + 0.59, // 0.59 is .594 rounded down
                'row_total_incl_tax' => 19.8 + 1.19,
                'base_row_total_incl_tax' => 9.9 + 0.59,
                'tax_amount' => 1.19,
                'base_tax_amount' => 0.59,
            ],
        ],
    ],
];
