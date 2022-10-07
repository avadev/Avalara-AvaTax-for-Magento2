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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
        'ClassyLlama_AvaTax/js/view/checkout-validation-handler'
    ],
    function (
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        checkoutValidationHandler
    ) {
        'use strict';

        return {
            saveShippingInformation: function() {
                var shippingAddress = {},
                payload;

                shippingAddress.extension_attributes = {
                    gift_registry_id: quote.shippingAddress().giftRegistryId
                };

                payload = {
                    addressInformation: {
                        shipping_address: shippingAddress,
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
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        quote.setTotals(response.totals);
                        // Begin Edit
                        checkoutValidationHandler.validationResponseHandler(response);
                        // End Edit
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
    }
);
