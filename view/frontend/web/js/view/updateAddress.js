define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        quote
    ) {
        'use strict';

        return function (address) {
            var quoteShippingAddress = quote.shippingAddress();
            var newAddress = $.extend(quoteShippingAddress, address);
            quote.shippingAddress(newAddress);
            if ($('input[name=billing-address-same-as-shipping]').filter(':checked').length) {
                quote.billingAddress(newAddress);
            }
        };
    }
);
