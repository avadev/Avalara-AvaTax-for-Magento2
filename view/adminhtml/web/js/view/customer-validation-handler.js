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
            validationContainer: '.validateAddressForm',
            validateButtonSelector: '.validateButton',
            validationRadioGroupName: 'addressToUse',

            validationResponseHandler: function (response, settings, form) {
                var self = this;
                $(form).find('.validateAddressForm').show();
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    addressValidationForm.fillValidateForm($(form).find(this.validationContainer));
                    if (!diffAddress.isDifferent()) {
                        alert({
                            title: $.mage.__('Success'),
                            content: $.mage.__('This address is already valid.')
                        });
                    } else {
                        this.selectAddressToUse(form);
                        $(form).find('input[name=' + this.validationRadioGroupName + ']:radio').on('change', function () {
                            self.selectAddressToUse(form);
                        });
                    }

                } else {
                    //$(this.options.validateAddressContainerSelector + ' *').hide();
                }
                $(this.validateButtonSelector).trigger('processStop');
            },

            selectAddressToUse: function (form) {
                if ($(form).find('#validAddress:checked').length) {
                    addressModel.selectedAddress(addressModel.validAddress());
                } else {
                    addressModel.selectedAddress(addressModel.originalAddress());
                }
                addressValidationForm.updateFormFields(form);
            }
        };
    }
);
