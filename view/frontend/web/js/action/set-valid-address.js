/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        quote,
        urlBuilder,
        storage,
        errorProcessor
    ) {
        'use strict';
        return function () {
            var payload;
            var serviceUrl;
            payload = {
                validAddress: {
                    validAddress: quote.shippingAddress()
                }
            };

            serviceUrl = urlBuilder.createUrl('/carts/mine/valid-address', {});

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        }
    }
);
