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
        'Magento_Ui/js/modal/alert',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/address-validation-form-admin'
    ],
    function (
        $,
        alert,
        addressModel,
        addressValidationForm
    ) {
        'use strict';

        return {
            validationResponseHandler: function (response, settings, form) {
                addressModel.error(null);
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    addressValidationForm.fillValidateForm(form, settings);
                    if (addressModel.error() == null && !addressModel.isDifferent()) {
                        alert({
                            title: $.mage.__('Success'),
                            content: $.mage.__('This address is already valid.')
                        });
                    }
                }
            }
        };
    }
);
