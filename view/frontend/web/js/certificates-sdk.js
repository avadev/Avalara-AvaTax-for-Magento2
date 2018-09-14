define(['sdkToken', 'jquery'], function (sdkToken, jQuery) {
    return function (container, params) {
        return jQuery.Deferred(function (defer) {
            sdkToken().then(function (sdkUrl, token, customerId) {
                require([sdkUrl], function () {
                    if (typeof params !== 'object') {
                        params = {};
                    }

                    params.token = token;
                    params.customer_number = customerId;

                    GenCert.init(container, params);
                    defer.resolve();
                });
            }).fail(defer.reject);
        });
    }
});
