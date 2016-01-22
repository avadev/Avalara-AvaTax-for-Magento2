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

$taxCalculationData['applied_taxes_one_rate'] = [
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
        'items' => [
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'simple1',
                'price' => 10,
                'qty' => 1,
            ],
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'simple2',
                'price' => 10,
                'qty' => 10,
            ],
        ],
    ],
    'expected_results' => [
        'compare_with_native_tax_calculation' => true,
        'address_data' => [
            'subtotal' => 110.0,
            'subtotal_incl_tax' => 116.6,
            'tax_amount' => 6.6,
            'grand_total' => 116.60,
            'applied_taxes' => [
                SetupUtil::AVATAX_MI_RATE_DESCRIPTION => [
                    'percent' => 6,
                    'amount' => 6.6,
                    'base_amount' => 6.6,
                    'rates' => [
                        [
                            'title' => SetupUtil::AVATAX_MI_RATE_DESCRIPTION,
                            'percent' => 6,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10,
                'tax_percent' => 6.0,
                'price' => 10,
                'price_incl_tax' => 10.6,
                'row_total_incl_tax' => 10.6,
                'tax_amount' => 0.6,
                'applied_taxes' => [
                    [
                        'percent' => 6,
                        'amount' => 0.6,
                        'base_amount' => 0.6,
                        'id' => SetupUtil::AVATAX_MI_RATE_DESCRIPTION,
                        'rates' => [
                            [
                                'code' => SetupUtil::AVATAX_MI_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_MI_RATE_DESCRIPTION,
                                'percent' => 6,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                ],
            ],
            'simple2' => [
                'row_total' => 100,
                'tax_percent' => 6.0,
                'price' => 10,
                'price_incl_tax' => 10.6,
                'row_total_incl_tax' => 106,
                'tax_amount' => 6.0,
                'applied_taxes' => [
                    [
                        'percent' => 6,
                        'amount' => 6,
                        'base_amount' => 6,
                        'id' => SetupUtil::AVATAX_MI_RATE_DESCRIPTION,
                        'rates' => [
                            [
                                'code' => SetupUtil::AVATAX_MI_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_MI_RATE_DESCRIPTION,
                                'percent' => 6,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                ],
            ],
        ],
    ],
];
