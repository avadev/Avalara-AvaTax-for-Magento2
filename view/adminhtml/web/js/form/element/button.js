/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'Magento_UI/js/form/element/abstract',
    'Magento_Ui/js/modal/alert',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/lib/serialize-form'
], function (
    $,
    utils,
    Abstract,
    alert,
    setCustomerAddress,
    addressModel
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

            namespace   = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        validateAddress: function (data, event) {
            var settings = {
                validationEnabled: this.validationEnabled,
                choice: this.choice,
                countriesEnabled: this.countriesEnabled,
                baseUrl: this.baseUrl
            };
            var form = $(event.target).closest(this.formSelector);
            var hasErrors = form.find('.admin__field-error:visible').length;
            if (!hasErrors) {
                var addressId = data.parentScope.match(/[0-9 -()+]+$/)[0];
                var addressObject = $(form).serializeObject()['address'][addressId];
                var inCountry = $.inArray(addressObject.country_id, settings.countriesEnabled.split(',')) >= 0;
                if (inCountry) {
                    addressModel.originalAddress(addressObject);
                    // TODO: Show 'Validating Address' spinner next to button instead of page
                    $(this.validateButtonSelector).trigger('processStart');
                    setCustomerAddress.validateAddress(settings, form);
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
                    content: $.mage.__('This address does not meet the requirements to be validated.')
                });
            }
        }
    });
});
