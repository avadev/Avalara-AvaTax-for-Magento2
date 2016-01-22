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
require_once __DIR__ . '/scenarios/customer_tax_class.php';
require_once __DIR__ . '/scenarios/tax_before_discount_only_applies_to_taxable_amount.php';
require_once __DIR__ . '/scenarios/tax_before_discount_applies_to_partial_taxable_amount.php';

/**
 * It would be valuable to add tests to cover these scenarios:
 *
 * - Gift wrapping (quote, printed card, items)
 * - Virtual product with no shipping address
 * - Multi address checkout (if it is reasonable to test this)
 */
