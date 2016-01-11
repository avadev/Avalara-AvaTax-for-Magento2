/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
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
