/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/action/validate-address-request',
    'ClassyLlama_AvaTax/js/view/validation-response-handler',

    // No object assigned to below dependencies
    'ClassyLlama_AvaTax/js/lib/serialize-form',
    'ClassyLlama_AvaTax/js/lib/event.simulate',
    'Magento_Ui/js/modal/modal',
    'prototype'
], function(
    jQuery,
    alert,
    addressValidationForm,
    addressModel,
    validateAddressRequest,
    validationResponseHandler
){

    jQuery.widget('ClassyLlama_AvaTax.addressValidationButton', jQuery.mage.modal, {
        options: {
            title: jQuery.mage.__('Verify Customer Address'),
            modalClass: 'validationModal',
            focus: '.validationModal .action-primary',
            responsive: true,
            closeText: jQuery.mage.__('Close'),
            buttons: [
                {
                    text: jQuery.mage.__('Edit This Address'),
                    class: 'action-secondary action-dismiss',
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    text: jQuery.mage.__('Use This Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        if (addressModel.error() == null) {
                            addressValidationForm.updateFormFields(this.addressForm);
                            if (addressModel.isDifferent() && addressModel.selectedAddress() == addressModel.validAddress()) {
                                if (this.addressType == 'billing' && jQuery('#order-shipping_same_as_billing').is(':checked')) {
                                    // Update shipping address with billing newly validation billing information.
                                    // Need to use Prototype to trigger event since events are bound with Prototype and
                                    // jQuery.trigger() doesn't work. See AdminOrder.bindAddressFields for bind logic.
                                    $('order-billing_address_fields')
                                        .select('input', 'select', 'textarea')
                                        .first()
                                        .simulate('change');
                                }
                                if (this.addressType == 'shipping') {
                                    // A simulated change is also necessary when changes are made to the shipping
                                    // address in order to update the available shipping methods.
                                    $('order-shipping_address_fields')
                                        .select('input', 'select', 'textarea')
                                        .first()
                                        .simulate('change');
                                }
                            }
                        }
                        this.closeModal();
                    }
                }
            ],
            hasChoice: 1
        },
        validationButtonContainer: ".validateAddressButton",
        validationContainer: '.validationModal .modal-content > div',
        validationForm: '#co-validate-form',
        editAddressLink: '.validateAddressForm a',
        addressForm: null,
        addressType: null,

        _create: function () {
            this._super();
            var self = this;

            addressValidationForm.bindTemplate(this.validationContainer, this.options, 'ClassyLlama_AvaTax/baseValidateAddress');

            jQuery(document).on('click', self.validationButtonContainer, function(event) {
                self.validateAddress(event);
            });
            // When the 'Edit this address' link in the instructions is clicked, close the modal
            jQuery(document).on('click', self.validationContainer + ' .instructions .edit-address', function () {
                self.closeModal();
            });
        },

        validateAddress: function (event) {
            var settings = {
                validationEnabled: this.options.validationEnabled,
                errorInstructions: this.options.errorInstructions,
                hasChoice: this.options.hasChoice,
                countriesEnabled: this.options.countriesEnabled
            };
            this.addressType = jQuery(event.target).data('address-type');
            var form = jQuery('#order-' + this.addressType + '_address');
            this.addressForm = form;
            var addressObject = jQuery(form).find(" *:input").serializeObject()['order'][this.addressType + "_address"];
            // The region field is empty initially and updated with js as the page loads but the value change is not
            // reflected in the dom so it is necessary to set the region field manually
            addressObject['region'] = jQuery("#order-" + this.addressType + "_address_region_id option[value='" + addressObject.region_id + "']").attr('title');
            addressModel.resetValues();
            var inCountry = jQuery.inArray(addressObject.country_id, settings.countriesEnabled.split(',')) >= 0;
            if (inCountry) {
                addressModel.originalAddress(addressObject);
                jQuery('body').trigger('processStart');
                var self = this;
                validateAddressRequest(this.options.baseUrl).done(function (response) {
                    addressModel.selectedAddress(addressModel.validAddress());
                    validationResponseHandler.validationResponseHandler(response, settings, self.validationContainer);
                    jQuery('.validateAddressForm').show();
                    if (addressModel.isDifferent() || addressModel.error() != null) {
                        self.openModal();
                    }
                    jQuery('body').trigger('processStop');
                }).fail(function () {
                    alert({
                        title: jQuery.mage.__('Error'),
                        content: jQuery.mage.__('The address could not be validated as entered. Please make sure all required fields have values and contain properly formatted values.')
                    });
                    jQuery('body').trigger('processStop');
                });
            } else {
                jQuery(form).find(this.validationContainer).hide();
                alert({
                    title: jQuery.mage.__('Error'),
                    content: jQuery.mage.__('Address validation is not enabled for the country you selected.')
                });
            }
        }
    });

    return jQuery.ClassyLlama_AvaTax.addressValidationButton;
});
