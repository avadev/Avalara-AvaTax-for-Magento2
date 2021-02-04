/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */
define([
    'jquery',
    'ClassyLlama_AvaTax/js/action/multishipping-save-address',
    'jquery/ui',
    'validation'
], function ($, multishippingSaveAddressAction) {
    'use strict';

    $.widget('mage.multishippingAddressValidation', {
        options: {
            selectors: {
                validAddressRadioSelector: '.validAddress',
                originalAddressRadioSelector: '.originalAddress',
                originalAddressTextSelector: ".originalAddressText",
                validAddressTextSelector: ".validAddressText",
                errorMessageContainerSelector: '.errorMessageContainer',
                addressOptionSelector: '.addressOption',
                addressRadioGroupName: 'addressToUse',
                selectedAddressClass: 'selected',
                addressValidationFormSelector: '.validateAddressForm',
                validationForm: '.address-validation-multicheckout',
                checkoutValidateAddressBlock: '.checkout-validate-address',
                editAddressLink: '.edit-address',
            }
        },

        /**
         * Validation creation
         * @protected
         */
        _create: function () {
            var self = this;
            self.toogleRadioButton();
            $(self.options.selectors.validationForm).find('input.av-radiobutton:radio[checked=checked]').trigger("change");
            $(self.options.selectors.editAddressLink).on('click', function () {
                location.href = $(this).parents('.block').find(".box-shipping-address a.action.edit").attr('href');
            })
        },

        toogleRadioButton: function () {
            var self = this;
            $(self.options.selectors.validationForm).find('input.av-radiobutton:radio').on('change', function () {
                $(this).parents(self.options.selectors.checkoutValidateAddressBlock)
                    .find(".selected")
                    .removeClass(self.options.selectors.selectedAddressClass)
                    .parent()
                    .find('input.av-radiobutton:checked')
                    .parents(self.options.selectors.addressOptionSelector)
                    .addClass(self.options.selectors.selectedAddressClass);

                multishippingSaveAddressAction(JSON.parse(this.dataset.address));
            });
        }
    });

    return $.mage.multishippingAddressValidation;
});
