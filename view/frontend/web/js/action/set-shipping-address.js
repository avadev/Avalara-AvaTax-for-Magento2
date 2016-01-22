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
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'ClassyLlama_AvaTax/js/view/update-address',
        'ClassyLlama_AvaTax/js/model/address-model'
    ],
    function (
        $,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        updateAddress,
        addressModel
    ) {
        'use strict';
        return function (validSelected) {
            var payload;

            if (validSelected) {
                updateAddress(addressModel.validAddress(), false);
            } else {
                updateAddress(addressModel.originalAddress(), false);
            }

            payload = {
                addressInformation: {
                    extension_attributes: {
                        should_validate_address: validSelected
                    },
                    // Since the updateAddress method above changes the shippingAddress, quote.shippingAddress()
                    // will return whichever option the user has submitted.
                    shipping_address: quote.shippingAddress(),
                    billing_address: quote.billingAddress(),
                    shipping_method_code: quote.shippingMethod().method_code,
                    shipping_carrier_code: quote.shippingMethod().carrier_code
                }
            };

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        }
    }
);
