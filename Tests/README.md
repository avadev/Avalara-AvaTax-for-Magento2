# General

This document assumes this extension has been installed via Composer and resides in `vendor/classyllama/module-avatax`.

# Test Coverage

The integration tests cover two things:

1. Build multiple quotes and request AvaTax tax rates for the quotes. The rates are then applied to the quotes and the values on the quote and quote items are verified against the "expected_results" arrays in the `vendor/classyllama/module-avatax/Tests/Integration/_files/scenarios/*.php` files to ensure they contain the proper values.
    
2. Since Magento may change how tax rates are applied to quotes and quote items, there are also tests in place that compare quotes/quote items after running through AvaTax tax calculation vs native Magento tax calculation. Scenarios with "compare_with_native_tax_calculation" set to true are compared to native Magento tax calculation. The following properties are using for determining which fields to compare:
    * `\ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\TaxTest::$quoteAddressFieldsEnsureMatch`
    * `\ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\TaxTest::$quoteAddressFieldsEnsureDiff`
    * `\ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\TaxTest::$quoteItemFieldsEnsureMatch`

The following scenarios are tested:

* Quote with a single applied rate
* Quote with multiple applied rates
* Simple products
* Bundled products with dynamic pricing
* Bundled products with simple pricing
* Configurable products
* Currency conversion, including rounding accuracy
* Customer tax classes (customer use types)
* Product tax classes (tax codes)
* Discounts
* Tax on shipping

# AvaTax Admin Configuration

The integration tests assume the following information has been configured in the AvaTax admin:

1. These tax jurisdictions have been setup:
    * Michigan (it has a 6% flat sales tax)
    * San Diego, California (we've used the San Diego Zoo: 2920 Zoo Dr, San Diego CA 92101, US)
    * Tennessee (it has a 9.25% flat sales tax)
2. A "D0000000" Tax Code has been created and an associated Tax Rule has been created that marks that Tax Code as tax exempt for the Michigan tax jurisdiction.
3. A "Base Override" Tax Rule has been created that changes Tennessee tax to 77% of the taxable amount

# Running Integration Tests

Follow these steps to run the integration tests:

1. Create a database for the integration tests (such as "magento_integration_tests")

    1. Update the `dev/tests/integration/etc/install-config-mysql.php.dist` file with your MySQL credentials.

1. You'll need an AvaTax development account setup with the rules specified in `vendor/classyllama/module-avatax/Tests/Integration/credentials.php.dist`

    1. Copy the `vendor/classyllama/module-avatax/Tests/Integration/credentials.php.dist` file to `vendor/classyllama/module-avatax/Tests/Integration/credentials.php` and update the "Company Code", "Account Number", and "License Key" values

1. Run the integration tests using this command: `vendor/bin/phpunit --debug -c <PATH_TO_INSTALL>/vendor/classyllama/module-avatax/Tests/Integration/phpunit.xml`
