
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/alert',
    'ClassyLlama_AvaTax/js/action/validate-address-request',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/view/validation-response-handler',
    'ClassyLlama_AvaTax/js/view/address-validation-form-admin',
    'ClassyLlama_AvaTax/js/lib/serialize-form'                      // jQuery serializeObject
], function (
    $,
    Abstract,
    alert,
    validateAddressRequest,
    addressModel,
    validationResponseHandler,
    addressValidationForm
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/form/address/element/button'
        },
        formSelector: 'aside .modal-component .form-inline fieldset',
        addressValidationFormSelector: '.avataxValidateAddressForm',
        radioGroupName: 'addressToUse',

        /**
         * Validate address
         *
         * @param data
         * @param event
         */
        validateAddress: function (data, event) {
            var self = this,
                settings = {
                validationEnabled: this.validationEnabled,
                hasChoice: 1,
                countriesEnabled: this.countriesEnabled,
                errorInstructions: this.errorInstructions,
                validationFormSelector: this.addressValidationFormSelector
                },
                form = $(event.target).closest(this.formSelector),
                hasErrors = form.find('.admin__field-error:visible').length;

            if (!hasErrors) {
                var addressObject = $(form).serializeObject(),
                    inCountry = $.inArray(addressObject.country_id, settings.countriesEnabled.split(',')) >= 0;
                if (inCountry) {
                    addressModel.originalAddress(addressObject);
                    $('body').trigger('processStart');
                    validateAddressRequest(this.baseUrl)
                        .done(function (response) {
                            addressModel.selectedAddress(addressModel.validAddress());
                            validationResponseHandler.validationResponseHandler(response, settings, form);
                            self.toggleAddressToUse(form);
                            if (addressModel.isDifferent() && addressModel.error() == null) {
                                addressValidationForm.updateFormFields(form);
                            }
                            jQuery('body').trigger('processStop');
                        })
                        .fail(function () {
                            alert({
                                title: $.mage.__('Error'),
                                content: $.mage.__('The address could not be validated as entered. Please make sure all required fields have values and contain properly formatted values.')
                            });
                            $('body').trigger('processStop');
                        });
                } else {
                    $(form).find(this.addressValidationFormSelector).hide();
                    alert({
                        title: $.mage.__('Error'),
                        content: $.mage.__('Address validation is not enabled for the country you selected.')
                    });
                }
            } else {
                $(form).find(this.addressValidationFormSelector).hide();
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('Please fix the form validation errors above and try again.')
                });
            }
        },

        /**
         * Toggle addresses
         *
         * @param form
         */
        toggleAddressToUse: function (form) {
            var self = this;
            $(form).find('input[name=' + self.radioGroupName + ']:radio').on('change', function () {
                addressValidationForm.updateFormFields(form);
            });
        }
    });
});
