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
            var serviceUrl;
            var payload = {
                address: quote.shippingAddress()
            };

            serviceUrl = urlBuilder.createUrl('/carts/mine/validate-address', {});

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    return response;
                }
            ).fail(
                function (response) {
                    return errorProcessor.process(response);
                }
            );
        }
    }
);
