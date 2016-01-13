define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/model/address-model'
    ],
    function (
        $,
        addressModel
    ) {
        'use strict';
        return function(url) {
            var payload = {
                address: addressModel.originalAddress()
            };
            return $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: payload
            });
        }
    }
);
