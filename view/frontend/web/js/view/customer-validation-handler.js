define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/address-validation-form'
    ],
    function (
        $,
        addressModel,
        addressValidationForm
    ) {
        'use strict';

        return {
            validationContainer: '.validationModal .modal-content > div',

            // TODO: maybe remove this function? It seems a bit too much to have this whole file for a single function
            validationResponseHandler: function (response) {
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    addressValidationForm.fillValidateForm(this.validationContainer);
                    $('body').trigger('processStop');
                }
            }
        };
    }
);
