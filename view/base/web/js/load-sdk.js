define(['jquery'], function (jQuery) {
    return function (container, params, sdkUrl, token, customerId) {
        return jQuery.Deferred(function (defer) {
            require([sdkUrl], function () {
                if (typeof params !== 'object') {
                    params = {};
                }

                params.token = token;
                params.customer_number = customerId;

                window.GenCert.init(container, params);
                defer.resolve(window.GenCert);
            });
        });
    }
});
