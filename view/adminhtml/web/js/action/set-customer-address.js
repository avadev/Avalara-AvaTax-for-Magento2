/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/customer-validation-handler'
    ],
    function (
        $,
        addressModel,
        customerValidationHandler
    ) {
        'use strict';
        return {
            validateAddress: function(settings, form) {
                var payload = {
                    address: addressModel.originalAddress()
                };
                $.ajax({
                    url: settings.baseUrl,
                    type: 'post',
                    dataType: 'json',
                    data: payload,
                    success: function (response) {
                        customerValidationHandler.validationResponseHandler(response, settings, form);
                    }
                });
            }
        }
    }
);
