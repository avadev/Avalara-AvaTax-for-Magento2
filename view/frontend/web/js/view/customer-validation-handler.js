define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/validation-form'
    ],
    function (
        $,
        addressModel,
        validationForm
    ) {
        'use strict';

        return {
            bindingElement: '.validate-binding',
            validationContainer: '.validationModal .modal-content .validate-binding',

            validationResponseHandler: function (response) {
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    validationForm.fillValidateForm();
                    $(this.bindingElement).trigger('processStop');
                }
            }
        };
    }
);
