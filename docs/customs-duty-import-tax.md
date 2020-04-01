<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Customs, Duty & Import Tax (CDIT)](./customs-duty-import-tax.md)
  - [Document Management (Tax Exemptions)](./document-management.md)

# Customs, Duty & Import Tax (CDIT)

## Table Of Contents

- [Overview](#overview)
- [Configuration](#configuration)
  * [Shipping Method Mapping](#shipping-method-mapping)
- [Known Issues](#known-issues)
- [Cross Border Class Management](#cross-border-class-management)
  * [Basic Steps to Setup Cross Border Classes](#basic-steps-to-setup-cross-border-classes)
  * [Example Cross Border Class - HS Code](#example-cross-border-class---hs-code)
- [Seller is Importer of Record Override](#seller-is-importer-of-record-override)

## Overview

>  Important Note: This extension's support for this Avalara feature requires that you [install version 2.x.x of the extension](./getting-started.md#version-notes).

This AvaTax connector for Magento provides a set of features to support Duty (hereafter referred to as CDIT). These features include:

- HS Code and Unit of Measure configuration for Products and their Destinations
- Map Native and Custom Shipping Methods with AvaTax’s shipping modes
- Display CDIT tax summaries in Emails, Checkout, and Admin Orders/Invoices/Credit Memos
- Configuring CDIT Passthrough and Non-Passthrough

## Configuration

1. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`. Click on the **AvaTax - Customs** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options](images/configuration_screenshot_2.0.0-rc1.png?raw=true).
3. CDIT will only work for countries that have AvaTax enabled for them, which is found under `AvaTax - General > Taxable Countries`

### Shipping Method Mapping

You can configure what Magento shipping methods map to AvaTax's shipping methods by selecting Magento Shipping methods in each of the AvaTax options of Air, Ocean, or Ground. The AvaTax connector only knows about the core shipping methods. If you use shipping methods from other extensions, you can utilize the `Custom Shipping Mode Mappings` table by specifying the AvaTax shipping mode and the custom shipping method code used by Magento.

For your convenience, you can also specify a default AvaTax shipping mode that will be used if no mapping was found in the aforementioned configuration properties. This can be useful if you typically only ship using one AvaTax mode.

## Known Issues

* Due to lack of support from the Avalara API, any HS Codes that have required "Unit of Measure" parameters can not be used.

## Cross Border Class Management

In order for Magento to properly send **HS Codes** and **Unit Amounts** to AvaTax for CDIT calculation, you must setup **Cross Border Classes**. A **Cross Border Class** defines a relation between **Cross Border Types** and destination countries, along with their relevant properties such as **HS Code**, **Unit Amount**, etc.

Magento utilizes **Cross Border Classes** by comparing the **Cross Border Type** defined on a product in the quote and the destination country specified in the ship to address of the quote. If there is a matching **Cross Border Class**, then that **Cross Border Class'** relevant properties are sent along to AvaTax when calculating tax. If a **Unit Name** and **Unit Amount Attribute** are specified on the **Cross Border Class**, then Magento retrieves the value of the **Unit Amount Attribute** on the product.

### Basic Steps to Setup Cross Border Classes

1. Create Cross Border Type
2. (Optional) Create Product Unit Value attribute and assign to attribute set
3. Create Cross Border Class
4. Assign Created Cross Border Type to Products
5. (Optional) Assign Unit Value to Products

### Example Cross Border Class - HS Code

Let's assume a customer is purchasing a V-Neck sweater. This V-Neck sweater, when shipped to Canada, should have an HS Code of 6109900090. To achieve this, we must do the following:

1. Create a Cross Border Type. For demonstration purposes, we created a Cross Border Type of **Vneck**
2. Create a Cross Border Class. This class will specify a destination country of CA, a Cross Border Type of **Vneck**, and an HS code of 6109900090
3. Modify our Magento product, **V-Neck Sweater**, to have a Cross Border Type of **Vneck**

After performing those steps, if we add **V-Neck Sweater** to our cart, proceed to checkout, and select a shipping address in Canada, we should now see CDIT tax displayed in the tax summary display in the cart.

## Seller is Importer of Record Override

By default, Magento does not send to AvaTax any value for `SellerIsImporterOfRecord`, but rather relies on your to configure your AvaTax account with the proper settings. However, should you need to modify this value on a per-customer basis, the AvaTax connector provides you the ability to specify on a customer record, whether to force the `SellerIsImporterOfRecord` value to be `Yes` or `No`.
