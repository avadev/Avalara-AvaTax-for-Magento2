<?php
$credentialsConfig = require __DIR__ . '/../credentials.php';

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
require_once __DIR__ . '/scenarios/product_tax_classes.php';

/**
 * TODO: Add tests for these scenarios:
 *
 * - Gift wrapping (quote, printed card, items)
 * - Product tax classes that make a product tax-exempt
 * - Customer tax classes that make a quote tax-exempt
 * - Virtual product with no shipping address
 * - Multi address checkout (if it is reasonable to test this)
 */
