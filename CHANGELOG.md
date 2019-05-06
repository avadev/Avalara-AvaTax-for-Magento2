### 1.5.7 (2019-05-06)
* Fixed issue where Incorrect tax details displayed for Canada transactions
* Updated support documentation

### 1.5.6 (2019-05-02)
* Fixed issue where Incorrect tax details displayed for Canada transactions

### 1.5.5 (2019-04-24)
* Fixed issue where Incorrect tax details displayed for Canada transactions

### 1.5.4 (2019-04-12)
* Fixed issue where Shipping Method Changing When "Same as Billing" Checked https://github.com/classyllama/ClassyLlama_AvaTax/issues/222
* Fixed issue where Restocking Fee Causing Unbalance errors/Warning Banner Can't be Disabled https://github.com/classyllama/ClassyLlama_AvaTax/issues/226

### 1.5.3 (2019-02-22)
* Added config fields to allow for calculating tax prior to discounts and send custom shipping tax code [PR #239](https://github.com/classyllama/ClassyLlama_AvaTax/pull/239) and [PR #231](https://github.com/classyllama/ClassyLlama_AvaTax/pull/231)
* Updated support documentation

### 1.5.1 (2019-01-30)
* Fix issue with payload extender in shipping-save-processor [#181](https://github.com/classyllama/ClassyLlama_AvaTax/issues/181)
* Add message about known issue with admin order creation [#215](https://github.com/classyllama/ClassyLlama_AvaTax/issues/215)

### 1.5.0 (2018-12-19)

* Add support for payload extender in shipping-save-processor [#181](https://github.com/classyllama/ClassyLlama_AvaTax/issues/181)
* Add support for Magento 2.3 [#108](https://github.com/classyllama/ClassyLlama_AvaTax/issues/108)

### 1.4.9 (2018-10-25)

* Fix issue where $0 rates are inaccurate in tax summary [PR #193](https://github.com/classyllama/ClassyLlama_AvaTax/pull/193)
* Fix issue where tax isn't calculated prior to checkout for virtual orders [#121](https://github.com/classyllama/ClassyLlama_AvaTax/issues/121)
* Fix issue where duplicate entries can be created in address book [#115](https://github.com/classyllama/ClassyLlama_AvaTax/issues/115)
* Refactor VAT ID submission code to match behavior described in config comment [PR #204](https://github.com/classyllama/ClassyLlama_AvaTax/pull/204)
* Refactor code to prevent rounding tax rate prior to displaying on frontend [#179](https://github.com/classyllama/ClassyLlama_AvaTax/issues/179)

### 1.4.8 (2018-10-15)

* Fix issue where editing customer in backend of Magento 2.1.x results in missing tabs (e.g. "Orders") [#151](https://github.com/classyllama/ClassyLlama_AvaTax/issues/151)
* Added sensitive and environment-specific entries to the configuration type pool [#178](https://github.com/classyllama/ClassyLlama_AvaTax/issues/178) (from @leoquijano)
* Add additional conditional check to prevent inadvertently disabling address validation [PR #124](https://github.com/classyllama/ClassyLlama_AvaTax/pull/124) (from @vovayatsyuk)

### 1.4.7 (2018-10-02)

* Fix issue where Magento 2.1.15 and 2.2.6 zero out shipping amount at checkout [#184](https://github.com/classyllama/ClassyLlama_AvaTax/issues/184)

### 1.4.6 (2018-09-27)

* Update the customer use codes for Religious and Educational classes to be separate types [#169](https://github.com/classyllama/ClassyLlama_AvaTax/issues/169)

### 1.4.5 (2018-09-14)

* Fix issue where saving AvaTax credentials at the website scope can result in an error [#171](https://github.com/classyllama/ClassyLlama_AvaTax/issues/171)

### 1.4.4 (2018-09-13)

* Fix issue where invalid/partial zip code values result in errors logged [#122](https://github.com/classyllama/ClassyLlama_AvaTax/issues/122)

### 1.4.3 (2018-07-13)

* Fix issue where address validation in admin while editing customer doesn't display region in original address [#135](https://github.com/classyllama/ClassyLlama_AvaTax/issues/135)
* Fix issue Save Address button remains disabled after closing address validation modal [#139](https://github.com/classyllama/ClassyLlama_AvaTax/issues/139)

### 1.4.2 (2018-06-25)

* Fix issue where Magento error isn't clear as to why a user can't proceed to payment method during checkout [#132](https://github.com/classyllama/ClassyLlama_AvaTax/issues/132)

### 1.4.1 (2018-04-12)

* Fix bug where exception is thrown when customer has no value for chosen attribute [#99](https://github.com/classyllama/ClassyLlama_AvaTax/issues/99)

### 1.4.0 (2018-04-03)

* Add the ability to use any customer attribute as customer code [#99](https://github.com/classyllama/ClassyLlama_AvaTax/issues/99)

### 1.3.5 (2018-03-05)

* Fix issue where sometimes the connection credentials fail when assigned at website level [#112](https://github.com/classyllama/ClassyLlama_AvaTax/issues/112)

### 1.3.4 (2018-02-14)

* Fix problem where customer can't save edited address in address book [#110](https://github.com/classyllama/ClassyLlama_AvaTax/issues/110)

### 1.3.3 (2018-02-12)

* Fix problem where customer can't save address in address book when address validation is disabled [#108](https://github.com/classyllama/ClassyLlama_AvaTax/issues/108)

### 1.3.2 (2018-02-08)

* Refactor code to prevent error when product is deleted before queue processing [#104](https://github.com/classyllama/ClassyLlama_AvaTax/issues/104)

### 1.3.1 (2018-01-31)

* Refactor code to make product ID retrieval more reliable [PR #103](https://github.com/classyllama/ClassyLlama_AvaTax/pull/103)
* Refactor code prevent error when running some CLI tests [PR #102](https://github.com/classyllama/ClassyLlama_AvaTax/pull/102)

### 1.3.0 (2017-12-29)

* Add the ability to use any custom customer attribute as customer code [#99](https://github.com/classyllama/ClassyLlama_AvaTax/issues/99)

### 1.2.7 (2017-12-14)

* Refactor code to remove abstract class for conditionally loading new class in parent constructor

### 1.2.6 (2017-12-03)

* Fix error when AvaTax extension has not set tax value as extension attribute [#93](https://github.com/classyllama/ClassyLlama_AvaTax/issues/93)

### 1.2.5 (2017-11-29)

* Fix error when processing queue for some invoices [#94](https://github.com/classyllama/ClassyLlama_AvaTax/issues/94)

### 1.2.4 (2017-11-27)

* Fix bug that prevents DI compilation [#85](https://github.com/classyllama/ClassyLlama_AvaTax/issues/85)

### 1.2.3 (2017-11-27)

* Refactor code for compatibility with Magento 2.2 [#85](https://github.com/classyllama/ClassyLlama_AvaTax/issues/85)

### 1.2.2 (2017-11-27)

* Refactor code to display verbose tax summary [#70](https://github.com/classyllama/ClassyLlama_AvaTax/issues/70)

### 1.2.1 (2017-10-15)

* Refactor code to exclude configurable products from Avalara submission [#78](https://github.com/classyllama/ClassyLlama_AvaTax/issues/78)
      
### 1.2.0 (2017-09-21)

* Refactor code to prepend 'AVATAX-' to jurisdiction tax code on all tax responses [#81](https://github.com/classyllama/ClassyLlama_AvaTax/issues/81)
    * This change will be reflected on the native Magento sales tax report and is for future tax requests only; it does
      not update existing tax results stored in Magento.

### 1.1.4 (2017-08-31)

* Refactor code to correctly use origin address line 2 [#77](https://github.com/classyllama/ClassyLlama_AvaTax/issues/77)
* Add JS to define baseUrl with correct value for address validation [#79](https://github.com/classyllama/ClassyLlama_AvaTax/issues/79)

### 1.1.3 (2017-07-28)

* Refactor code to remove XSS vulnerability [#74](https://github.com/classyllama/ClassyLlama_AvaTax/issues/74)

### 1.1.2 (2017-07-10)

* Refactor code to accommodate installations with table prefixes [#67](https://github.com/classyllama/ClassyLlama_AvaTax/issues/67)

### 1.1.1 (2017-06-19)

* Refactor code to utilize Mode config setting at a store view level [#68](https://github.com/classyllama/ClassyLlama_AvaTax/issues/68)

### 1.1.0 (2017-04-27)

* Add support for global tax calculation using the IsSellerImporterOfRecord parameter in the Avalara API [#57](https://github.com/classyllama/ClassyLlama_AvaTax/issues/57)
    * More information regarding how this parameter is utilized can be found in the [AvaTax Extension documentation](https://www.classyllama.com/documentation/extensions/avatax-magento-2-module#importer_of_record).

### 1.0.2 (2017-05-19)

* Add code to send Magento Order Number to Avalara as "Reference Code" attribute. See [documentation](https://www.classyllama.com/documentation/extensions/avatax-magento-2-module#sales-numbers) for details.

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
