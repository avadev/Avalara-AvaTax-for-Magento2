<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
## Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Sales Tax](./sales-tax.md)
  - [Address Validation](./address-validation.md)
  - [Customs, Duty & Import Tax (CDIT)](./customs-duty-import-tax.md)
  - [Document Management (Tax Exemptions)](./document-management.md)

# Document Management

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
  * [SDK Credentials](#sdk-credentials)
  * [Checkout Link Text](#checkout-link-text)
- [Frontend Features](#frontend-features)
- [Backend Features](#backend-features)

## Overview

>  Important Note: This extension's support for this Avalara feature requires that you [install version 2.x.x of the extension](./getting-started.md#version-notes).

This AvaTax connector for Magento provides a set of features to support Document Management (also referred to as Tax Exemptions). These features include:

- Reviewing Tax Exemption Documents for customers
- Adding & Removing Tax Exemption Documents for customers
- Adding Tax Exemption Documents during checkout

When a **Tax Exemption** is applied to a customer's account, tax is automatically removed from their cart when shipping to that Exemption's region.

## Configuration

1. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`. Click on the **AvaTax - Document Management** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options.](images/configuration_screenshot_2.0.0-rc1.png?raw=true)

### SDK Credentials

In order to use Document Management (CertCapture), you'll need to ensure that your account has CertCapture API access enabled.

To connect to CertCapture, you'll need to add your SDK credentials to your Magento installation's `app/etc/env.php` file. Here is [an example `env.php` file](files/env.php), showing the `cert-capture` array added to the file.

**`env.php` Development Configuration**

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

**`env.php` Production Configuration**

```
<?php
return [
  // ...
  'cert-capture' => [
    'url' => 'https://api.certcapture.com/v2/auth/get-token',
    'sdk-url' => 'https://app.certcapture.com/gencert2/js',
    'auth' => [
      'username' => '', // Certcapture username
      'password' => '' // Certcapture password
    ],
    'client-id' => '' // The certcapture client id you will use
  ],
  // ...
];
```

> In the Magento admin (`Stores > Settings > Configuration > Sales > Tax > AvaTax - General`), there is a setting called **Mode** that allows an admin to toggle between Development and Production mode. That setting is _not_ respected for CertCapture—you'll need to configure the `env.php` file differently for each environment. Long-term, the Avalara API will be upgraded to support generating tokens for Document Management, and at that point this `env.php` configuration will no longer be necessary.

Retrieve the 3 credentials above using these steps:

* Create a CertCapture user that will specifically be used for the API authentication.
	* Login
	    * Development - Login to https://app.certcapture.com/
	    * Production - Login to https://sbx.certcapture.com/
	* Go to "Settings > Account Settings > Manage Users"
	* Click "Add User"
		* Name: "Magento 2 API User"
		* Email: It's recommended to use a company email, rather than one linked to a specific individual (for example, "apiuser@example.com")
		* User Role: API User
		* Status: Active
	* Login to that newly created user account. Click the "My Profile" link at the top right of the page. Click on the "REST API Access" tab. Input a password. You'll use that password for the "cert-capture > auth > password" value in the `env.php` file, and you'll use the email in the "cert-capture > auth > username" value.
* To retrieve the `client-id`, go to "Settings > Company Settings > Company Details" and use the "Company ID" value that is listed on that page as your `client-id`.

### Checkout Link Text

You can configure what text you want to display to a user during checkout to initiate the Document Management workflow. These options include:

- Add certification when the customer has no certifications (also used for guests)
- Add certification when the customer has certifications
- Manage existing certifications



## Frontend Features

Document Management is supported on the frontend for:

1. Allowing customers to view certificates on their account

1. Allow customers to delete certificates from their account

1. Allow customers to add certificates from any supported region

1. Allow customers to add certificates during checkout for their current destination

![](images/document-management-features.jpg?raw=true)



## Backend Features

Document Management is supported on the frontend for:

1. View a customer's certificates
2. Delete a customer's certificates
3. Add certificates from any supported region to a customer's account

![](images/document-management-backend.jpg?raw=true)
