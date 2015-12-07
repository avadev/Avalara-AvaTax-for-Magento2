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
            // TODO: Find a more consistent method to check for when that checkbox is selected
            if ($('input[name=billing-address-same-as-shipping]:checked')) {
                quote.billingAddress(newAddress);
            }
        };
    }
);
