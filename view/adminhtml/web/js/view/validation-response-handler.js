define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/address-validation-form',
        'ClassyLlama_AvaTax/js/view/diff-address'
    ],
    function (
        $,
        alert,
        addressModel,
        addressValidationForm,
        diffAddress
    ) {
        'use strict';

        return {
            validationResponseHandler: function (response, settings, form) {
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    addressValidationForm.fillValidateForm(form, settings);
                    if (addressModel.error() == null && !diffAddress.isDifferent()) {
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
