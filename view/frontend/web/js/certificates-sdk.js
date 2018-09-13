define(['sdkToken'], function (sdkToken) {
    return function(container, params) {
        new Promise(function (resolve, reject) {
            sdkToken().then(function (sdkUrl, token, customerId) {
                require([sdkUrl], function () {
                    if(typeof params !== 'object') {
                        params = {};
                    }

                    params.token = token;
                    params.customer_number = customerId;

                    GenCert.init(container, params);
                    resolve();
                });
            }).fail(reject);
        });
    }
});