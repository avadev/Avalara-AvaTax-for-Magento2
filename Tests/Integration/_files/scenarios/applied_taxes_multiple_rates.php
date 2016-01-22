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

$sanDiegoAddress = [
    'postcode' => SetupUtil::SAN_DIEGO_POST_CODE,
    'country_id' => SetupUtil::COUNTRY_US,
    'city' => SetupUtil::SAN_DIEGO_CITY,
    'street' => [SetupUtil::SAN_DIEGO_STREET_1],
    'region_id' => SetupUtil::REGION_CA,
];

$taxCalculationData['applied_taxes_multiple_rates'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => $credentialsConfig,
    ],
    'quote_data' => [
        'billing_address' => $sanDiegoAddress,
        'shipping_address' => $sanDiegoAddress,
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
            'subtotal_incl_tax' => 118.8,
            'tax_amount' => 8.8,
            'grand_total' => 118.80,
            'applied_taxes' => [
                SetupUtil::AVATAX_CA_RATE_DESCRIPTION
                    . ' - ' . SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION
                    . ' - ' . SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION
                        => [
                    'percent' => 8,
                    'amount' => 8.8,
                    'base_amount' => 8.8,
                    'rates' => [
                        [
                            'code' => SetupUtil::AVATAX_CA_RATE_JURISCODE,
                            'title' => SetupUtil::AVATAX_CA_RATE_DESCRIPTION,
                            'percent' => 6.5,
                        ],
                        [
                            'code' => SetupUtil::AVATAX_CA_COUNTY_RATE_JURISCODE,
                            'title' => SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION,
                            'percent' => 1,
                        ],
                        [
                            'code' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_JURISCODE,
                            'title' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION,
                            'percent' => 0.5,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10,
                'tax_percent' => 8.0,
                'price' => 10,
                'price_incl_tax' => 10.8,
                'row_total_incl_tax' => 10.8,
                'tax_amount' => 0.8,
                'applied_taxes' => [
                    [
                        'percent' => 8,
                        'amount' => 0.8,
                        'base_amount' => 0.8,
                        'id' => SetupUtil::AVATAX_CA_RATE_DESCRIPTION
                            . ' - ' . SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION
                            . ' - ' . SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION,
                        'rates' => [
                            [
                                'code' => SetupUtil::AVATAX_CA_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_RATE_DESCRIPTION,
                                'percent' => 6.5,
                            ],
                            [
                                'code' => SetupUtil::AVATAX_CA_COUNTY_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION,
                                'percent' => 1,
                            ],
                            [
                                'code' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION,
                                'percent' => 0.5,
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
                'tax_percent' => 8.0,
                'price' => 10,
                'price_incl_tax' => 10.8,
                'row_total_incl_tax' => 108,
                'tax_amount' => 8.0,
                'applied_taxes' => [
                    [
                        'percent' => 8,
                        'amount' => 8,
                        'base_amount' => 8,
                        'id' => SetupUtil::AVATAX_CA_RATE_DESCRIPTION
                            . ' - ' . SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION
                            . ' - ' . SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION,
                        'rates' => [
                            [
                                'code' => SetupUtil::AVATAX_CA_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_RATE_DESCRIPTION,
                                'percent' => 6.5,
                            ],
                            [
                                'code' => SetupUtil::AVATAX_CA_COUNTY_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_COUNTY_RATE_DESCRIPTION,
                                'percent' => 1,
                            ],
                            [
                                'code' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_JURISCODE,
                                'title' => SetupUtil::AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION,
                                'percent' => 0.5,
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
