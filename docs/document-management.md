<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Customs, Duty & Import Tax (CDIT)](./customs-duty-import-tax.md)
  - [Document Management (Tax Exemptions)](./document-management.md)

# Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
  * [SDK Credentials](#sdk-credentials)
  * [Checkout Link Text](#checkout-link-text)
- [Frontend Features](#frontend-features)
- [Backend Features](#backend-features)

# Overview

This AvaTax connector for Magento provides a set of features to support Document Management (also referred to as Tax Exemptions). These features include:

- Reviewing Tax Exemption Documents for customers
- Adding & Removing Tax Exemption Documents for customers
- Adding Tax Exemption Documents during checkout

When a **Tax Exemption** is applied to a customer's account, tax is automatically removed from their cart when shipping to that Exemption's region.

# Configuration

In order to utilize Document Management, you must first enable it. Document Management configuration options are located under `Stores > Configuration > Sales > Tax > AvaTax - Document Management`.



## SDK Credentials

In order to connect to cert capture, you'll need to add your SDK credentials to your Magento installation's `app/etc/env.php`:

```
<?php
return [
  // ...
  'cert-capture' => [
    'url' => 'https://sbx-api.certcapture.com/v2/auth/get-token',
    'sdk-url' => 'https://sbx.certcapture.com/gencert2/js',
    'auth' => [
      'username' => '', // Certcapture username
      'password' => '' // Certcapture password
    ],
    'client-id' => '' // The certcapture client id you will use
  ],
  // ...
];
```



## Checkout Link Text

You can configure what text you want to display to a user during checkout to initiate the Document Management workflow. These options include:

- Add certification when the customer has no certifications (also used for guests)
- Add certification when the customer has certifications
- Manage existing certifications



# Frontend Features

Document Management is supported on the frontend for:

1. Allowing customers to view certificates on their account

1. Allow customers to delete certificates from their account

1. Allow customers to add certificates from any supported region

1. Allow customers to add certificates during checkout for their current destination

![](images/document-management-features.jpg?raw=true)



# Backend Features

Document Management is supported on the frontend for:

1. View a customer's certificates
2. Delete a customer's certificates
3. Add certificates from any supported region to a customer's account

![](images/document-management-backend.jpg?raw=true)
