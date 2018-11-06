define(['ClassyLlama_AvaTax/js/action/get-sdk-token', 'ClassyLlama_AvaTax/js/load-sdk'], function (sdkToken, loadSdk) {
    return function (tokenUrl, customerId, container, params) {
        return sdkToken(tokenUrl, customerId).then(function (sdkUrl, token, customerId) {
            return loadSdk(container, params, sdkUrl, token, customerId);
        });
    }
});
