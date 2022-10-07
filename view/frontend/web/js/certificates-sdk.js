define(['ClassyLlama_AvaTax/js/action/get-sdk-token', 'ClassyLlama_AvaTax/js/load-sdk'], function (sdkToken, loadSdk) {
    return function (container, params) {
        return sdkToken().then(function (sdkUrl, token, customerId) {
            return loadSdk(container, params, sdkUrl, token, customerId);
        });
    }
});
