/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/model/url-builder',
        'mage/storage'
    ],
    function (
        addressModel,
        urlBuilder,
        storage
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
                    // TODO: implement custom error processor
                    //return errorProcessor.process(response);
                }
            );
        }
    }
);
