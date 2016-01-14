define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/address-validation-form'
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
