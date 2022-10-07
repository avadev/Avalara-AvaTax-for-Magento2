<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
## Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Cross-Border](./customs-duty-import-tax.md)
  - [Tax Exemption Certificates](./document-management.md)

# Tax Exemption Certificates

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Set up](#set-up)
- [Magento's ENV.PHP Configuration Update](#magentos-envphp-configuration-update)
- [Frequently Asked Questions and Related Links](#frequently-asked-questions-and-related-links)
- [Troubleshooting](#troubleshooting)
- [Frontend Features Overview](#frontend-features-overview)
- [Backend Features Overview](#backend-features-overview)
- [CertCapture for eCommerce SDK Back-end Configuration Settings](#certcapture-for-ecommerce-sdk-back-end-configuration-settings)

## Overview

>  Important Note: This extension's support for this Avalara feature requires version 2.1.8 of the extension. If your already using the
   Magento AvaTax extension make sure you're running 2.1.8 or higher. You can see what version you're running by logging into your
   Magento instance Stores > Settings > Configuration > Sales > Tax > AvaTax -General > AvaTax Extension Version.

>  Heads up! This feature requires you to have Avalara CertCapture Enterprise before you can use this feature! Please [contact Avalara](https://avlr.co/2YuiDkM)
   to set up.

This AvaTax connector for Magento provides a set of features to support Document Management (also referred to as Tax Exemption Certificates). This
feature includes:

- The ability for Seller to review customer certificate status, remove, print, download Exemption Certificates
- The ability for Seller to update customer information and invite customers to fill out Exemption Certificates
- Customers can review certificate status, remove, download and add Exemption Certificates
- Customers can fill out an Exemption Certificate during checkout

When an **Exemption Certificate** is applied to a customer's account; tax is automatically removed from their cart when shipping to that Exemption region,
once the certificate is approved.

## Requirements

- AvaTax for Magento 2 version 2.1.8 or higher
- Install and configure the integration using Composer by following [Getting Started](getting-started.md)
- CertCapture Enterprise and User Access; [contact Avalara to get set up](https://avlr.co/2YuiDkM)
    - [Get Started using CertCapture](https://help.avalara.com/0021_Avalara_CertCapture)
- Single Use Exemption Certificates are NOT supported by default see FAQ below for more details.

## Set up

I.  Enable Document Management in Magento
 
In the Magento admin, go to Stores > Settings > Configuration > Sales > Tax. Click on the **AvaTax - Document Management** section -
Enable Document Management set to Yes


II.  Review Configuration Settings

**Default Magento Configuration**
- Checkout Link Text
    - You can configure what text you want to display to a user during checkout to initiate the Document Management workflow. These
      options include:
        - Add certification when the customer has no certifications (also used for guests)
        - Add certification when the customer has certifications
        - Manage existing certifications
    - Change the Status name of the Certificate 
        - You can set the "Approved" status to another way of letting your customer know the certificate is ready for use
        - You can set the "Denied" status to another message like "Pending", "Pending Approval", "Please Contact Us at"

III. Test Customer Workflow

**Default CertCapture Workflow**
- Customer Record Creation - 2 methods
    - Direct from Cart 
        - If it is the customer's first time providing a certificate through your Magento site their customer record will be created in
          CertCapture during the certificate creation/upload process. Once their customer record is stored in CertCapture if they return
          they will not need to re-enter their contact details
    - From Customer Account Page
        - sophia how do you do it this way in M2
- Certificate Validation
    - Certificates created using the Document Management UI will be automatically set to valid and attached to the customer record in
      CertCapture
    - Certificates uploaded will NOT be automatically validated
    
## Frequently Asked Questions and Related Links

[Avalara Help Center - CertCapture FAQ's](https://help.avalara.com/Frequently_Asked_Questions/CertCapture_-_Knowledge_Base)

**Why don't single-use Exemption Certificates work?**
A. Currently, the integration has no default support for passing a PurchaseOrder value. With other 3rd party plugin's with Purchase Order number
support; your System Integrator can easily customize the integration by mapping your Purchase Order numbers on calls to AvaTax. 

**How are Exemption Certificates by the customer identified in AvaTax?**
A. The customerCode passed in a request to calculate tax is passed to AvaTax and the customerCode matched with the ShipTo location will determine if
a Customer Tax Certificate will apply. You can configure the customerCode in the Magento AvaTax integration by going to Stores > Configuration > Sales > Tax > AvaTax-General > Data Mapping > Customer Code Format. IMPORTANT: Select a value that will uniquely ID your customer. 

**Why is my tax not getting removed after the certificate is created?**
A. This is likely a result of the certificate being in PENDING status, rather than being in APPROVED status (someone hasn't gotten around to validating
your certificate yet).

## Troubleshooting

Having trouble? Please check these steps before posting a support request:

- Check the [documentation](../README.md) to ensure that the plugin is configured properly.
- Please ensure that you meet the requirements.
- Check the FAQs to see if they address your question.

## Frontend Features Overview

Document Management is supported on the frontend for:
1. Allowing customers to view certificates on their account - My Account > Tax Certificates > View Certificate
1. Allow customers to delete certificates from their account - My Account > Tax Certificates > Delete Certificate 
1. Allow customers to add certificates from any supported region - My Account > Tax Certificates > Add Exemption
1. Allow customers to add certificates during checkout for their current destination

![](images/exemption-certificates-1.png?raw=true)

## Backend Features Overview 

Document Management is supported on the frontend for:

1. View a customer's certificates - Customers > All Customers > Edit > Tax Certificates > View Certificate 
1. Delete a customer's certificates - Customers > All Customers > Edit > Tax Certificates > Delete Certificate
1. Invite a customer to create a certificate from any supported region to a customer's account - Customers > All Customers > Edit > Tax Certificates > Invite a Customer to Add a Certificate
1. Update a customers information - Customers > All Customers > Edit > Tax Certificates > Update Customer Infomation at Avalara

## CertCapture for eCommerce SDK Back-end Configuration Settings

Review the link below for examples of use cases and further configuration options if needed: 
- [Install CertCapture for eCommerce](https://help.avalara.com/0021_Avalara_CertCapture/All_About_CertCapture/Install_CertCapture_for_eCommerce?origin=deflection)

Customizing the configuration settings directly in the code in Magento may lead to unexpected conflicts with CertCapture, AvaTax and other parts of
Magento. Make sure you have a qualified Magento developer to make any custom code changes and be sure to test any changes before releasing to your
production store.
