<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Cross-Border](./customs-duty-import-tax.md)
  - [Tax Exemption Certificates](./document-management.md)

# Sales Tax

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Product Tax Codes](#product-tax-codes)
- [Use UPC Attribute as Item Code](#use-upc-attribute-as-item-code)
- [Customer Usage Type (or Entity Use Code)](#customer-usage-type-or-entity-use-code)
- [AvaTax Queue](#avatax-queue)
  * [Unbalanced Queue Items](#unbalanced-queue-items)
- [AvaTax Logging](#avatax-logging)
- [VAT Tax](#vat-tax)
- [Magento Order and Invoice Numbers](#magento-order-and-invoice-numbers)

## Overview

In Magento, tax calculation typically occurs during checkout but may also happen at other times as well (e.g., shopping cart). This extension will calculate tax via the AvaTax API as soon as the customer submits a postal code, either via the **Estimate Shipping and Tax** form on the cart or via the **Shipping Address** form during the checkout process. When an order is placed, the amount of tax for that order is calculated by AvaTax, but the tax "record" is not immediately recorded in AvaTax. Since Magento supports multiple invoices and multiple credit memos for the same order, orders are not recorded as a whole in AvaTax. Tax amounts are calculated for the order when the customer places the order, but nothing is recorded in AvaTax until a new invoice or credit memo is created. Refer to the eCommerce chart on [this AvaTax documentation page](https://developer.avalara.com/avatax/use-cases/) for a visualization of the process. 

A cron task runs every five minutes to send invoices and credit memos to AvaTax. The status of each pending item can be found in the AvaTax Queue in `Stores > AvaTax Queue`. The Magento CRON must be configured in order for the extension to work properly. If you're testing the extension in an environment is not configured (such as a development or staging environment), you can manually process they queue by clicking the **Process Queue Now** button on the `Stores > AvaTax Queue` page.

## Configuration

1. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`. Click on the **AvaTax - General** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options.](images/configuration_screenshot_2_x.png?raw=true)
3. The comment text underneath each of the options in this section should explain the purpose of the setting, but here are some notes about some of the settings:
   - <a name="filter_by_region">**Filter Tax Calculation By Region**
       - Avalara's recommendation is to leave this option set to the default of **No**. With this option set to **No**, Magento will contact Avalara's API for all regions when tax is being calculated in Magento. This will result in more API calls to AvaTax, however based on how Avalara charges for API calls, the impact of these additional API calls may be minimal or non-existent. Read more about how Avalara charges for API calls [here](https://www.avalara.com/us/en/legal/avatax-terms.html). If your site has a large number of people calculating tax (whether in the cart or checkout), but not placing an order, then the 10:1 ratio of "API calls" vs "Documents Recorded" may make it more expensive to have all API calls sent to Avalara for regions where taxes are not being calculated. Here is an overview of how many API calls are made for a standard Magento checkout:
           - Guest checkout (user adds one product to cart, proceeds to checkout, enters shipping address, and then finishes placing order): 3 API calls are sent to Avalara ([screenshot](https://github.com/astoundcommerce/avatax/blob/develop/docs/images/screenshot_tax_requests_customer.jpg?raw=true))
           - Customer checkout (user logs in, adds one product to cart, proceeds to checkout, leaves pre-existing shispping address selected, and then finishes placing order): 2 API calls are sent to Avalara ([screenshot](https://github.com/astoundcommerce/avatax/blob/develop/docs/images/screenshot_tax_requests_guest.jpg?raw=true))
       - If you change the option to **Yes**, Magento will only contact the AvaTax API for regions where you have a tax nexus. However this may cause issues in the future if you need to see all historical transactions in Avalara, and it might affect report reconciling. Talk to your Avalara support representative before changing this to **No**.
       - This setting does _not_ limit API requests for Address Validation
   - **Data Mapping — Shipping SKU, Adjustment Refund SKU, Adjustment Fee SKU, Gift Wrap Order SKU, Gift Wrap Items SKU, and Gift Wrap Printed Card SKU:** SKUs sent to AvaTax for the associated event. For example, when tax is requested for a single-product order sent to state X, it's possible state X charges tax on shipping. Therefore, two products will be sent in the request: one for the cart item and another for shipping. The correct shipping tax code (FR020100) will always be sent; however, this allows you to customize the SKU in case you want to add custom functionality in your AvaTax dashboard. The same is true when creating a Credit Memo with an adjustment refund or fee in the Magento Admin.
   - **Set Seller as Importer of Record for Global Transactions:** By default, Avalara will use the origin address when calculating sales tax for global transactions (generally resulting in a $0.00 tax amount). Enabling this setting will cause Avalara to calculate sales tax based on the destination address for countries indicated as taxable in the **Taxable Countries** selector. For more information on what it means to be the **Importer of Record**, visit the [Avalara Help Center](https://help.avalara.com/000_Avalara_AvaTax/Manage_Transactions/Manage_Place_of_Supply_settings).

## Product Tax Codes

Many merchants will not need to use product tax codes. Refer to the [AvaTax documentation](https://help.avalara.com/000_AvaTax_Calc/000AvaTaxCalc_User_Guide/051_Select_AvaTax_System_Tax_Codes/Tax_Codes_-_Frequently_Asked_Questions) to learn about tax codes. Consult with your Avalara representative if you are uncertain whether you need to use them. 

Native Magento has builtin **Tax Classes** (not be confused with AvaTax's **Tax Codes**) and it uses those Tax Classes for its internal tax calculation via Tax Rules. If you are using the AvaTax extension for tax calculation then you should not setup Tax Rules, however this extension does use Tax Classes in order to associate Magento products with AvaTax Tax Codes.

1. In the Magento admin, go to `Stores > Product Tax Classes`.
   1. Click the **Create New Tax Class** button.
   2. Enter a **Class Name** (can be anything you want) and an **AvaTax Tax Code**.
   3. Click **Save Tax Class**.
2. In the Magento admin, go to `Catalog > Products`.
   1. Select the product that you want to associate with your newly created Tax Class.
   2. In the **Tax Class** dropdown, select your newly created Tax Class and click **Save**.
3. Now, when this product is sent to the AvaTax API, the associated **AvaTax Tax Code** will be sent in the **TaxCode** field.
4. Follow the steps above for all of the AvaTax Tax Codes that you want to use in Magento.

## Use UPC Attribute as Item Code

AvaTax has support for using a UPC as a Item Code, although this is only relevant for certain product categories (apparel, etc). To send UPC codes as Item Code, follow these steps:

1. Create a product attribute that will store the UPC code for your products. The attribute type must be **text**.
2. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`.
   1. Click on the **AvaTax Settings** section.
   2. Select your UPC attribute from the **UPC Attribute To Use As Item Code** dropdown.
   3. Click **Save Config**.
3. Now, when a product is sent to the AvaTax API, if that product has a value in the UPC attribute, it will be sent in the **ItemCode** field.

## Customer Usage Type (or Entity Use Code)

Many merchants will not need to use Customer Usage Type. Unless you have customers with special tax exemptions, you most likely do not need to set this up. If you are unsure, contact your AvaTax representative for more information or refer to the [AvaTax documentation](https://help.avalara.com/kb/001/What_are_the_exemption_reasons_for_each_Entity_Use_Code_used_for_Avalara_AvaTax%3F). **Entity Use Code** is synonymous with **Customer Usage Type**.

1. In the Magento admin, go to `Stores > Customer Tax Classes`.
   1. Click the **Create New Tax Class** button.
   2. Enter a **Class Name** (can be anything you want) and select the appropriate value from the **AvaTax Customer Usage Type** dropdown.
   3. Click **Save Tax Class**
2. In the Magento admin, go to `Stores > Customer Groups`.
   1. Either create a new Customer Group or select an existing one.
   2. In the **Tax Class** dropdown, select your newly created Tax Class and click **Save**.
3. In the Magento admin, go to *Customers > All Customers*.
   1. Edit the customer that you want to associate with the Customer Usage Type.
   2. Click the **Account Information** tab.
   3. Select the appropriate Customer Group from the **Group** dropdown.
4. Now, when this customer places an order, the associated Customer Usage Type will be sent to the AvaTax API in the **CustomerUsageType** field.

If you are utilizing customer groups in a way that mixes taxable and tax exempt customers within the same group(s), then you would need to consider custom development to accommodate exempting specific customers from sales tax. A possible solution is the introduction of a plugin for the [_\ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForCustomer_](https://github.com/astoundcommerce/avatax/blob/develop/Helper/TaxClass.php) method that could read the value of a custom attribute for a customer and replace the CustomerUsageType for the customer’s assigned customer group with the appropriate value (e.g. ‘F’ = Religious/Education) to achieve tax exempt status for the lookup.

## AvaTax Queue

The AvaTax Queue functionality only works when **Tax Mode** is set to **Estimate Tax & Submit Transactions to AvaTax.** The following section assumes that AvaTax queueing is enabled. To view the AvaTax Queue, in the Magento admin, go to `Stores > AvaTax Queue`. 

When invoices and credit memos are created in Magento, new records are added to the AvaTax Queue with a **pending** status. If a CRON job is properly configured, then every 5 minutes, all pending records will be submitted to AvaTax with a **Document Type** of **Sales Invoice** or **Return Invoice**, depending on whether the record is a Magento invoice or credit memo (respectively). If there are errors submitting the record, Magento will attempt to resend the record for the number of times configured in the **Max Queue Retry Attempts** field. 

If you are in a development or staging environment and don't have a CRON job setup, you can manually send queued records to AvaTax using the **Process Queue Now** button on the `Stores > AvaTax Queue` page.

You could choose the way how to process items in the queue. There are two options for the  `Configuration -> Tax -> AvaTax Advanced -> Queue Processing Type`, Normal and Batch.

### Unbalanced Queue Items

Occasionally you may see queue items with a **Queue Status** of **Complete** and a **Message** of something like *"Unbalanced Response - Collected: 11.8400, AvaTax Actual: 11.86"*. In order to understand what an unbalanced queue item is, you need to understand the Magento/AvaTax tax calculation workflow (the example is for an invoice, but same thing applies to credit memos):

- Customer goes through checkout process and provides shipping address.
- Magento connects to AvaTax's API to retrieve tax rates.
- Magento applies AvaTax's tax rates to shopping cart (i.e., quote).
- Customer places order.
- Magento copies AvaTax tax rates from shopping cart to order.
- Depending on how the order's payment method is configured, an invoice will either be created at the time of order creation or at some later point. Whenever the invoice is created, Magento will copy the tax values from the order to the invoice. In situations where multiple invoices are created per order, Magento will do the best it can of evenly splitting the tax from the order to the invoices for that order.
- Once an invoice is created, it gets added to the AvaTax Queue and the invoice information is submitted to the AvaTax API and the tax amount for that new API request is returned to Magento.
- Magento then compares the tax amount from the new API request to the amount of tax that Magento copied from the order. If the amounts match (most common scenario), then the queue item is set to "Complete" with no message. If the amounts don't match, the queue item is set to "Complete" and a message will be added indicating that the amounts are unbalanced. This is a screenshot of both a balanced and an unbalanced queue
  ![](images/avatax_queue.png?raw=true)

These are the most common reasons an invoice or credit memo could become unbalanced:

- Multiple invoices/credit memos are created for an order and there are slight differences in how Magento copies tax from the order vs how AvaTax calculates tax for the invoice/credit memo.
- Since tax rates and configurations can change, the tax amount of orders can also change from the time the customer checks out and when orders are invoiced.

If an invoice or credit memo is unbalanced, a comment will be added to the order with the same unbalanced information that is present on the **AvaTax Queue** grid **Message** column (this is important since queue records are eventually deleted). This extension adds two tables to the Magento database (specifically the _sales_ database when using [Magento Enterprise's split database mode](http://devdocs.magento.com/guides/v2.1/config-guide/multi-master/multi-master.html)): `avatax_sales_creditmemo` and `avatax_sales_invoice` 

For reference, here is a screenshot of the `avatax_sales_invoice` table with some example entries:  

![](images/avatax_sales_invoice.png?raw=true)

These tables are not used for anything, but if you need to generate custom reports on unbalanced amounts, you can use these fields within each:

- `parent_id` – This links this row to the sales\_invoice or sales\_creditmemo tables, based on the "entity\_id" field of those tables.
- `is_unbalanced` – Set to “1” if the record is unbalanced and “0” if the record is balanced.
- `base_avatax_tax_amount` – The amount of tax that AvaTax calculated for the invoice or credit memo.

Note: An entry is not made in these tables for an invoice or credit memo until it has been submitted to AvaTax. 

Note: Prior to version 0.4.0 of this extension, two fields (avatax\_is\_unbalanced and base\_avatax\_tax\_amount) were added to the sales\_invoice and sales\_creditmemo tables that tracked this information. Per the [0.4.0 release notes](https://github.com/astoundcommerce/avatax/releases/tag/0.4.0), if a merchant upgrades to 0.4.0, the columns on those tables will be migrated to the avatax\_sales\_invoice and avatax\_sales\_creditmemo tables mentioned above.

## AvaTax Logging

The logging functionality built into this extension is for debugging purposes. If you are experiencing issues with this extension, you can review the logs to see if they provide any details about the issues you are experiencing. 

This extension can log information in two locations: In files (in the var/log/ directory) and/or in the database (in `Stores > AvaTax Logs`), depending on the logging settings you have configured in `Stores > Settings > Configuration > Sales > Tax > AvaTax Settings > Logging Settings`.

## VAT Tax

AvaTax supports calculating VAT tax, assuming you have AvaTax with Global Calculation. If a customer places an order in a jurisdiction with VAT taxing, then this extension will calculate the appropriate amount of tax to charge. However this extension only calculates tax once a customer has provided their postal code, either via the **Estimate Shipping and Tax** section on the cart or by providing their shipping address in the checkout process. Since many VAT taxing jurisdictions require that VAT tax must be displayed anywhere product prices are displayed, you must use Magento's native tax calculation to handle tax calculation in the catalog (product listing, product detail, search, etc) and then AvaTax will take over the calculation once the customer has provided a postal code. 

If you need to display product prices including VAT tax, you should follow the steps in the [Magento documentation](http://docs.magento.com/m2/ce/user_guide/tax/vat-validation-configure.html) to configure your site to charge VAT tax. Once you have done that, Magento's native tax calculation will be used until the user has provided a postal code, at which point AvaTax will be used to determine VAT tax calculation.

## Magento Order and Invoice Numbers

If you're using AvaTax with a **Tax Mode** of **Estimate Tax & Submit Transactions to AvaTax**, when Invoices or Credit Memos get sent to AvaTax, the Invoice/Credit Memo number will be sent in the **Purchase Order No** field and the Magento Order Number will get sent in the **Reference Code** field. See this screenshot of the AvaTax interface for an example of where to find these numbers: 

![](images/avatax_sales_numbers.png?raw=true)

