<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Cross-Border](./customs-duty-import-tax.md)
  - [Tax Exemption Certificates](./document-management.md)

# Cross-Border

## Table Of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Setup](#setup)
- [Configure target countries](#configure-target-countries)
- [Classify product catalog](#classify-product-catalog)
- [Enabling Cross Border in Magento for AvaTax](#enabling-cross-border-in-magento-for-avatax)
- [Shipping Method Mapping](#shipping-method-mapping)
- [Cross-border reporting](#cross-border-reporting)
- [Frequently asked questions](#frequently-asked-questions)
- [Troubleshooting](#troubleshooting)

## Overview

>  Important Note: This extension's support for this Avalara feature requires version 2.1.8 of the extention. If your already using the
   Magento AvaTax extension make sure your running 2.1.8 or higher. You can see what version your running by logging into your
   Magento instance Stores > Settings > Configuration > Sales > Tax > AvaTax -General > AvaTax Extension Version.

>  Heads up! Cross-border must be enabled on your account by Avalara before you can use this feature! Please [contact Avalara](https://avlr.co/2EcfQWD) to
   set up cross-border.

[Cross-Border by Avalara](https://www.avalara.com/us/en/products/industry-solutions/cross-border-solutions.html) helps merchants that sell internationally calculate the Duty and Import Tax assessed at the border for their shipped products and
process payment for those fees at checkout. This lets you **reduce customs delay and remove unexpected fees**, ensuring an optimal experience for
international purchases as they pay all fees at checkout and receive their shipments on time as expected.

In this guide, we’ll walk through how to setup Cross-Border by Avalara so it can be used with Magento 2 and AvaTax.

This AvaTax connector for Magento provides a set of features to support Customs Duty and Import Tax to display CDIT tax summaries in Emails,
Checkout, and Admin Orders/Invoices/Credit Memos

## Requirements
- AvaTax for Magento 2 version 2.1.8 or higher
- Install and configure the integration using Composer by following [Getting Started](./getting-started.md)
- A service entitlement enabled for your Avalara account; [contact Avalara to get this setup](https://avlr.co/2EcfQWD)
- You **must enter prices exclusive of tax**

## Setup
There is three key tasks to setup:

- Configuring target countries to identify the countries where you collect and sell to in AvaTax
- Classifying your product catalog so that Avalara knows your Magento product IDs and the harmonized system / tariff codes (HS codes) for those
products and any applicable [Avalara Tax Codes](https://taxcode.avatax.avalara.com/)
- Enabling Cross Border in Magento for AvaTax 

## Configure target countries
First, you’ll need to setup the countries that you want to sell to in your Avalara account. You’ll need to provide some information in Avalara about your
status in these jurisdictions, such as:

- Do you have a permanent establishment in this jurisdiction?
- When shipping to this jurisdiction, who is the importer of record?
- Are you shipping using a Delivery Duty Paid (DDP) or Delivery at Place (DAP) service?

Follow the steps below to configure target countries:

a. Login to your Avalara account and go to **Settings > Where you collect tax**

![](images/woocommerce-avatax-cross-border-setup-countries-1.png?raw=true)

b. Select the **Customs duty** tab, then click **Add a country where you want to collect customs duty.**

![](images/woocommerce-avatax-cross-border-setup-countries-2.png?raw=true)

c. Select one or more countries to add and click **Add selected countries**.

![](images/woocommerce-avatax-cross-border-setup-countries-3.png?raw=true)

d. Find the newly-added country in the Nexus list and click **Details**

![](images/woocommerce-avatax-cross-border-setup-countries-4.png?raw=true)

e. Update the country’s detail page as needed and click **Save**.

**Note:**
If you intend to collect all costs from your customers, select the **This company is the importer of record** setting.

![](images/woocommerce-avatax-cross-border-setup-countries-5.png?raw=true)

a. Repeat steps 4-5 as needed for each country you’ve added.
b. Now, go back to **Settings > Where you collect tax** and select the **VAT / GST** tab. Repeat steps 3-5 as needed.

![](images/woocommerce-avatax-cross-border-setup-countries-6.png?raw=true)

## Classify product catalog

Once your countries are configured, it’s time to ensure your product catalog is added to AvaTax with the harmonized tariff codes and classification codes
required to get accurate landed cost estimates for your products in Magento.

There are essentially two options for accomplishing this step:
- Send products to Avalara via SFTP or API
- Add products to Avalara manually
- Use Avalara Item Classification: Avalara Item Classification uses AI to automate the process of identifying and mapping your products for
shipment to any country and every jurisdiction in the world. [contact Avalara to get this setup](https://avlr.co/2EcfQWD)

**Send products to Avalara via SFTP or API**

You can import your products to Avalara with several methods. [Click here for guides on how to send products to Avalara and map their tariff codes.](https://help.avalara.com/Avalara_Item_Classification_and_Cross-border/Map_the_items_you_sell_to_tariff_codes)

If you decide to send the products via SFTP, you must provide the products in the Google product feed format. We recommend creating a product export.

Here are some additional resources to assist with creating the Google product feed:
- You can export your products directly from Magento 2 by going to System > Data Transfer > Export
- [AvaTax guidelines for importing Google product catalogs](https://help.avalara.com/Avalara_Item_Classification_and_Cross-border/Guidelines_for_importing_Google_product_catalogs)
    - Please note that while this document recommends using the product SKU for the Google product feed’s id field, you **must** use the
   product ID from Magento instead. This will allow the plugin to find the product in AvaTax based on the AvaTax Item Code
    - [Guide to Google product feed](https://docs.google.com/spreadsheets/d/1j39cY_J_VLQrc9XTeLsRIVvJXgxADVrg0xbf1u39h7c/edit?usp=sharing) / [Magento product export mapping](https://docs.google.com/spreadsheets/d/1j39cY_J_VLQrc9XTeLsRIVvJXgxADVrg0xbf1u39h7c/edit?usp=sharing)

**Add products to Avalara manually**

Alternatively, you can also add products manually by following the steps below:

1. Go to **Settings > What you sell.**
2. Click **Add an item**
3. Update the item page with the following details:
    - Enter the **Item Code**. **The Item Code should match the Magento product ID for this product**
    - Enter the **Item Description**. This can be the product’s name.
    - Update the **Avalara tax code** field.
    - Under **Harmonized tariff codes**, click **Add or update tariff codes** and populate the **country** and **tariff code** fields
4. Repeat step 4 for every country that you want to sell this product in.
5. Click **Save and get attributes**.
6. Update the product attributes if required.
7. Click **Save**.

Repeat steps 2-7 for every product you need to sell internationally.

![](images/woocommerce-avatax-cross-border-setup-product-catalog.png?raw=true)

When an order is placed on Magento, the plugin will now be able to find the product’s based on their AvaTax itemCode and AvaTax can then return the
proper rates based on the entered country and tariff codes.

## Enabling Cross Border in Magento for AvaTax
1. In the Magento admin, go to Stores > Settings > Configuration > Sales > Tax. Click on the **AvaTax - Customs** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options](images/configuration_screenshot_2_x.png?raw=true)
3. CDIT will only work for countries that have AvaTax enabled for them, which is found under AvaTax - General > Taxable Countries

## Shipping Method Mapping

You can configure what Magento shipping methods map to AvaTax's shipping codes. The AvaTax connector only knows about the core shipping methods.
If you use shipping methods from other extensions, you can utilize the Custom Shipping Mode Mappings table by specifying the AvaTax shipping
codes and the custom shipping method code used by Magento.

For your convenience, you can also specify a default AvaTax shipping code that will be used if no mapping was found in the aforementioned configuration
properties. This can be useful if you typically only ship using one AvaTax mode.

## Checkout experience

Once you’ve completed the setup steps above, Magento AvaTax will automatically present tariffs and duties at checkout.

If you, as the merchant, are the **importer of record**, estimated fees and duties will be added to checkout.

If you are not the **importer of record**, the fees will be estimated.

## Cross-border reporting

Assuming you are logging transactions to AvaTax you can download a report on your cross-border transactions, login to Avalara, go to **Reports >
Transaction** reports, and select “Cross border reports” from the **Report Category** drop-down menu. You can select other filters / limits as desired, and
then click **Generate and download report** to create the report.

![](images/woocommerce-avatax-cross-border-report.png?raw=true)

## Frequently asked questions
**Q: Why can’t I enter product prices inclusive of tax?**
A: Avalara’s API does not yet support calculating tax from an inclusive price, so this isn’t possible right now in the plugin. We’ll continue to monitor this so
we can support it in the plugin once possible in the API.

**Q: I don’t see cross-border fees at checkout like I expect. What might be wrong?**
A: It sounds like you may not be listed as the importer of record or your SKU getting passed to AvaTax is not matching or not mapped to the appropriate
HS Code. Please ensure that the **This company is the importer of record** setting is enabled in Avalara for the country in question. 

Make sure you have the correct ItemCode setup in AvaTax to match the itemCode getting passed to AvaTax from Magento. 

Make sure your items in AvaTax have the appropriate HS Code assigned for the country your shipping into.

## Troubleshooting
Having trouble? Please check these steps before posting a support request:
- Check the [documentation](../README.md) to ensure that the plugin is configured properly.
- Please ensure that you meet the requirements for Cross-Border and contact your Avalara representative to ensure they’ve enabled Cross-Border
  on your account.
- Check the FAQs to see if they address your question.
