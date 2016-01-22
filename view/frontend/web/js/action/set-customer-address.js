/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
