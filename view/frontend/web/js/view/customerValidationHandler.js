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
            validationContainer: '.validationModal .modal-content > div',
            bindingElement: '.validate-binding',

            validationResponseHandler: function (response) {
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    validationForm.fillValidateForm();
                    $(this.bindingElement).trigger('processStop');
                } else {
                    //$(this.options.validateAddressContainerSelector + ' *').hide();
                }
            }
        };
    }
);
