/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/alert',
    'ClassyLlama_AvaTax/js/action/validate-address-request',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/view/validation-response-handler',
    'ClassyLlama_AvaTax/js/view/diff-address',
    'ClassyLlama_AvaTax/js/view/address-validation-form',

    // No object assigned to below dependencies
    'ClassyLlama_AvaTax/js/lib/serialize-form'
], function (
    $,
    _,
    utils,
    Abstract,
    alert,
    validateAddressRequest,
    addressModel,
    validationResponseHandler,
    diffAddress,
    addressValidationForm
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: ''
            },
            template: 'ClassyLlama_AvaTax/form/element/button'
        },

        addressComponentSelector: '.address-item-edit',
        formSelector: '.address-item-edit-content fieldset',
        validateButtonSelector: '.validateButton',
        addressValidationFormSelector: '.validateAddressForm',
        radioGroupName: 'addressToUse',
        selectedClass: 'selected',


        /**
         * Initializes file component.
         *
         * @returns {Media} Chainable.
         */
        initialize: function () {
            this._super()
                .initFormId();

            return this;
        },

        /**
         * Defines form ID with which file input will be associated.
         *
         * @returns {Media} Chainable.
         */
        initFormId: function () {
            var namespace;

            if (this.formId) {
                return this;
            }

            namespace = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        validateAddress: function (data, event) {
            var self = this;
            var settings = {
                validationEnabled: this.validationEnabled,
                hasChoice: this.choice,
                countriesEnabled: this.countriesEnabled,
                errorInstructions: this.errorInstructions,
                validationFormSelector: this.addressValidationFormSelector
            };
            var form = $(event.target).closest(this.formSelector);
            var hasErrors = form.find('.admin__field-error:visible').length;
            if (!hasErrors) {
                // Match numbers
                var addressId = data.parentScope.match(/[0-9 -()+]+$/)[0];
                var addressObject = $(form).serializeObject()['address'][addressId];
                var inCountry = $.inArray(addressObject.country_id, settings.countriesEnabled.split(',')) >= 0;
                if (inCountry) {
                    addressModel.originalAddress(addressObject);
                    // TODO: Show 'Validating Address' spinner next to button instead of page
                    $('body').trigger('processStart');
                    validateAddressRequest(this.baseUrl).done(function (response) {
                        addressModel.selectedAddress(addressModel.validAddress());
                        validationResponseHandler.validationResponseHandler(response, settings, form);
                        self.toggleAddressToUse(form);
                        if (diffAddress.isDifferent && addressModel.error() == null) {
                            addressValidationForm.updateFormFields(form);
                        }
                        $('body').trigger('processStop');
                    }).fail(function () {
                        alert({
                            title: jQuery.mage.__('Error'),
                            content: jQuery.mage.__('The address could not be validated as entered. Please make sure all required fields have values and contain properly formatted values.')
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
                // TODO: change this error message to something more clear
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('Please fix the form validation errors above and try again.')
                });
            }
        },

        toggleAddressToUse: function (form) {
            var self = this;
            $(form).find('input[name=' + self.radioGroupName + ']:radio').on('change', function () {
                addressValidationForm.updateFormFields(form);
            });
        }
    });
});
