### 1.1.0 (2017-04-27)

* Add support for global tax calculation using the IsSellerImporterOfRecord parameter in the Avalara API [#57](https://github.com/classyllama/ClassyLlama_AvaTax/issues/57)
    * More information regarding how this parameter is utilized can be found in the [AvaTax Extension documentation](https://www.classyllama.com/documentation/extensions/avatax-magento-2-module#configuration).

### 1.0.1 (2017-04-18)

* Revise error when no credentials are set for the chosen mode to instead display as a warning with more verbosity [#60](https://github.com/classyllama/ClassyLlama_AvaTax/issues/60)
    
### 1.0.0 (2017-04-14)

* Add support for [Magento Enterprise's split database mode](http://devdocs.magento.com/guides/v2.1/config-guide/multi-master/multi-master.html) [#54](https://github.com/classyllama/ClassyLlama_AvaTax/issues/54)
    * Refactor code to create AvaTax tables in the 'sales' database when running in split database mode
    * Reference issue [#54](https://github.com/classyllama/ClassyLlama_AvaTax/issues/54) for additional notes and details

### 0.4.0 (2017-03-14)

* Add code to create new database tables dedicated to storing AvaTax data
* Add code to migrate existing data from AvaTax columns on sales_invoice and sales_creditmemo tables to new tables
* Refactor code to store AvaTax data in new tables instead of attaching to entities
* Previous versions of this extension added two fields to the native Magento invoice and credit memo tables. When this extension
  changed the values of these two fields, it would save the invoice/credit memo. This caused multiple issues (see [#24](https://github.com/classyllama/ClassyLlama_AvaTax/issues/24), [#29](https://github.com/classyllama/ClassyLlama_AvaTax/issues/29), [#36](https://github.com/classyllama/ClassyLlama_AvaTax/issues/36), [#40](https://github.com/classyllama/ClassyLlama_AvaTax/issues/40), and [#47](https://github.com/classyllama/ClassyLlama_AvaTax/issues/47)). 
  Rather than continuing to try and fix the underlying Magento issues that were triggered by saving these objects, we have moved
  the fields to separate database tables (avatax_sales_creditmemo and avatax_sales_invoice). This release includes that refactor and 
  fixes issue [#47](https://github.com/classyllama/ClassyLlama_AvaTax/issues/47).

### 0.3.5 (2017-03-17)

* Fix issue where gift card purchases are taxed [#53](https://github.com/classyllama/ClassyLlama_AvaTax/issues/53)

### 0.3.4 (2017-02-04)

* Fix issue where refunding an online credit memo would result in duplicate refund amounts being set on order and order status changing [#36](https://github.com/classyllama/ClassyLlama_AvaTax/issues/36) and [#40](https://github.com/classyllama/ClassyLlama_AvaTax/issues/40)
* Fix issue where tax is calculated for $0 carts [#39](https://github.com/classyllama/ClassyLlama_AvaTax/issues/39)
* Fix error when invoice with a single $0 item is sent to AvaTax [#46](https://github.com/classyllama/ClassyLlama_AvaTax/issues/46)

### 0.3.3 (2016-11-09)

* Fix issue where Magento order number was not being sent to AvaTax in the PurchaseOrderNo field [#38](https://github.com/classyllama/ClassyLlama_AvaTax/issues/38) (from @expandlab)

### 0.3.2 (2016-09-14)

* Use store view-specific TaxCodes for invoice/credit memos [#34](https://github.com/classyllama/ClassyLlama_AvaTax/issues/34)
* Fix potential tax inaccuracy when order/invoice are created on different dates [#33](https://github.com/classyllama/ClassyLlama_AvaTax/issues/33)
* Fix issue where saving "un-verifiable" customer address results in error [#27](https://github.com/classyllama/ClassyLlama_AvaTax/issues/27) 
* Fix validation errors flagged by Magento Marketplace

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
