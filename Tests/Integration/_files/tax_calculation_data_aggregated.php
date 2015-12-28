<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$credentialsConfig = require __DIR__ . '/credentials.php';
/**
 * Global array that holds test scenarios data
 *
 * @var array
 */
$taxCalculationData = [];

require_once __DIR__ . '/scenarios/configurable_product.php';
require_once __DIR__ . '/scenarios/currency_conversion_rounding.php';
require_once __DIR__ . '/scenarios/applied_taxes_one_rate.php';
require_once __DIR__ . '/scenarios/applied_taxes_multiple_rates.php';
require_once __DIR__ . '/scenarios/bundled_product_dynamic_pricing.php';
require_once __DIR__ . '/scenarios/bundled_product_fixed_pricing.php';

// TODO: WIP
//require_once __DIR__ . '/scenarios/comprensive_test.php';
//require_once __DIR__ . '/scenarios/bundled_products.php';
