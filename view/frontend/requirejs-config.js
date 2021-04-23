/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

var config = {
    map: {
        '*': {
            "Magento_Checkout/js/model/shipping-save-processor/gift-registry": 'ClassyLlama_AvaTax/js/model/shipping-save-processor/gift-registry',
            "Magento_Tax/template/checkout/cart/totals/tax": 'ClassyLlama_AvaTax/template/checkout/cart/totals/tax',
            "Magento_Checkout/template/payment-methods/list": 'ClassyLlama_AvaTax/template/payment-methods/list',
            "Magento_Tax/template/checkout/summary/tax": 'ClassyLlama_AvaTax/template/checkout/summary/tax',
            multiShippingAddressValidation: 'ClassyLlama_AvaTax/js/multishipping-address-validation',
            // Add the following alias to provide compatibility with Magento 2.2
            addressValidation: 'ClassyLlama_AvaTax/js/addressValidation',
            deleteCertificate: 'ClassyLlama_AvaTax/js/delete-certificate',
            addressValidationModal: 'ClassyLlama_AvaTax/js/view/checkout-billing-address-validation-modal'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'ClassyLlama_AvaTax/js/view/payment/list/certificates-link': true
            },
            'Magento_Tax/js/view/checkout/summary/tax': {
                'ClassyLlama_AvaTax/js/view/checkout/summary/tax/mixin': true,
                'ClassyLlama_AvaTax/js/view/payment/list/certificates-link': true
            },
            'Magento_Tax/js/view/checkout/cart/totals/tax': {
                'ClassyLlama_AvaTax/js/view/checkout/summary/tax/mixin': true
            },
            'Magento_Checkout/js/view/estimation': {
                // We can leverage the same login from the tax summary to determine if we have customs
                'ClassyLlama_AvaTax/js/view/checkout/summary/tax/mixin': true,
                'ClassyLlama_AvaTax/js/view/estimation/mixin': true
            },
            'Magento_Checkout/js/model/step-navigator': {
                'ClassyLlama_AvaTax/js/model/step-navigator/mixin': true
            },
            'ClassyLlama_AvaTax/js/action/account-add-exemption': {
                'ClassyLlama_AvaTax/js/customer-account-add-exemption': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'ClassyLlama_AvaTax/js/model/shipping-save-processor/default': true
            }
        }
    }
};
