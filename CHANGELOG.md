### 0.3.1 (2016-06-30)

* Fix issue where invoice/credit memo may be mistakenly sent to AvaTax multiple times. See https://github.com/classyllama/ClassyLlama_AvaTax/issues/24

### 0.3.0 (2016-06-30)

* Add support for Magento 2.1 (and drop support for Magento 2.0.x)
* Fix to address region lookup when validating addresses (from @curtisTAG)
* Add API for address validation (from @james481). See https://github.com/classyllama/ClassyLlama_AvaTax/pull/15

### 0.2.4 (2016-06-09)

* Fix 'Class "storeId" does not exist' error when loading the Swagger page: /rest/default/schema (Github issue #19)

### 0.2.3 (2016-05-31)

* Fix "$serverData must be an array or object implementing ArrayAccess" error when single tenant compilation was in use

### 0.2.2 (2016-05-17)

* Fix DI compilation error

### 0.2.1 (2016-05-16)

* Fix issue where manual module installation causes DI compilation error
* Add Magento_Tax as a module dependency (from @james481)
* Add license

### 0.2.0 (2016-04-12)

* Fix issue where tax calculation was not accurate for certain merchant accounts
    * Changed DocType for cart/checkout tax calculation from PurchaseOrder to SalesOrder
    * PurchaseOrder is intended to be used for "consumer use tax" calculation rather than "sales tax" calculation

### 0.1.16 (2016-04-05)

* Add support for PHP 5.5

### 0.1.15 (2016-04-01)

* Add support for PHP 7

### 0.1.14 (2016-03-30)

* Fix issue where tax_amount is incorrectly calculated when multiple rates are in use
* Fix issue where saving customer address on frontend resulted in error

### 0.1.13 (2016-03-25)

* Add AvaTax Code to Gift Card products on Magento Enterprise

### 0.1.12 (2016-03-09)

* Simplify log view page

### 0.1.11 (2016-03-09)

* Fix DI compilation error on Magento Community
* Display admin address validation beneath custom address attributes on Magento Enterprise

### 0.1.10 (2016-02-17)

* Fix DI compilation error

### 0.1.9 (2016-02-16)

* Add ability to configure Account Number and License Key at store view scope
* Add ability to configure Company Code at store view scope
* Fix issue where certain SOAP exceptions were not being logged
* Fix issue Address Validation bugs

### 0.1.8 (2016-02-15)

* Change UPC logic to send UPC in ItemCode field
* Add readme for integration tests

### 0.1.7 (2016-02-05)

* Add ability to filter tax calculation by country or region
* Fix compilation error

### 0.1.6 (2016-02-03)

* Fix access when using production credentials

### 0.1.5 (2016-02-03)

* Fix CSS compilation issue

### 0.1.4 (2016-01-27)

* Initial beta release
