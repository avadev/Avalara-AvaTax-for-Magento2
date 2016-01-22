/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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

        return function (address, dontCheckForBillingAddress) {
            var newAddress = [];
            // The address being returned is meant to be stored on the quote in the database, not on the quote object
            // in the browser. The keys on the server side quote use snake case while the keys on the client side quote
            // use camel case. This loop converts the keys from snake case to camel case so the client side quote will
            // have the keys the server is expecting to see on the client side quote.
            $.each(address, function (key, value) {
                var newKey = key.replace(/_([a-z])/g, function (g) { return g[1].toUpperCase(); });
                newAddress[newKey] = value;
            });

            var quoteShippingAddress = quote.shippingAddress();
            newAddress = $.extend(quoteShippingAddress, newAddress);
            quote.shippingAddress(newAddress);

            // dontCheckForBillingAddress allows for the billing address to be updated even when billing address same
            // as shipping is not checked. This is necessary because the checkbox isn't always checked by the time this
            // line is executed. This is only the case on the initial loading of the Review & Payments step so the
            // dontCheckForBillingAddress property is set to true on the initial loading of that step and false when
            // switching between the suggested and original address.
            if ($('input[name=billing-address-same-as-shipping]').filter(':checked').length || dontCheckForBillingAddress) {
                quote.billingAddress(newAddress);
            }
        };
    }
);
