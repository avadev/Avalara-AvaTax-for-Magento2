define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/model/url-builder'
    ],
    function (
        $,
        storage,
        alert,
        addressModel,
        urlBuilder
    ) {
        'use strict';
        return function () {
            var serviceUrl;
            var payload = {
                address: addressModel.originalAddress()
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
                    var messageObject = JSON.parse(response.responseText);
                    alert({
                        title: $.mage.__('Error'),
                        content: messageObject.message
                    });
                }
            );
        }
    }
);
