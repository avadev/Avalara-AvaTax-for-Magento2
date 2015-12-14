define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/model/addressModel',
        'ClassyLlama_AvaTax/js/view/validationForm'
    ],
    function (
        $,
        addressModel,
        validationForm
    ) {
        'use strict';

        return {
            options: {
                validateAddressContainerSelector: '#validate_address'
            },

            validationResponseHandler: function (response) {
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    validationForm.fillValidateForm();
                } else {
                    //$(this.options.validateAddressContainerSelector + ' *').hide();
                }
            }
        };
    }
);
