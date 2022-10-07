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
    'ko',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-converter',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
    'Magento_Ui/js/modal/modal'
], function(
    $,
    ko,
    addressModel,
    setCustomerAddress,
    addressConverter,
    addressValidationForm
){

    $.widget('ClassyLlama_AvaTax.addressValidationModal', $.mage.modal, {
        options: {
            title: $.mage.__('Verify Your Address'),
            modalClass: 'validationModal',
            focus: '.validationModal .action-primary',
            responsive: true,
            closeText: $.mage.__('Close'),
            buttons: [
                {
                    text: $.mage.__('Edit Address'),
                    class: 'action-secondary action-dismiss',
                    click: function () {
                        this.enableSubmit($(this.formSelector));
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Save Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        if (addressModel.isDifferent()) {
                            addressValidationForm.updateFormFields(this.formSelector);
                        }
                        this.closeModal();
                        $(this.formSelector).off('submit');
                        $(this.formSelector).submit();
                    }
                }
            ]
        },
        validationContainer: '.validationModal .modal-content > div',
        formSelector: '.form-address-edit',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();

            this.handleFormSubmit();
            addressValidationForm.bindTemplate(this.validationContainer, this.options, 'ClassyLlama_AvaTax/baseValidateAddress');
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " .edit-address").on('click', function () {
                self.closeModal();
            });
        },

        closeModal: function () {
            this._super();
            this.enableSubmit($(this.formSelector));
        },

        handleFormSubmit: function () {
            var self = this;

            // Set the status of the AvaTax address validation config setting for use elsewhere in form processing;
            // specifically form submission
            $(self.formSelector).data('avataxAddressValidationEnabled', self.options.validationEnabled);

            $(this.formSelector).on('submit', function (e) {
                if (self.options.validationEnabled) {
                    try {
                        $('.validateAddressForm').show();
                        var isValid = $(':mage-validation').validation('isValid');
                        if (isValid) {
                            e.preventDefault();
                            addressModel.error(null);
                            var formData = $(self.formSelector).serializeObject();
                            var addressObject = addressConverter.formAddressDataToCustomerAddress(formData);
                            var inCountry = $.inArray(addressObject.countryId, self.options.countriesEnabled.split(',')) >= 0;
                            if (inCountry) {
                                addressModel.originalAddress(addressObject);
                                $('body').trigger('processStart');
                                setCustomerAddress().done(function (response) {
                                    if (typeof response === 'string') {
                                        addressModel.error(response);
                                    } else {
                                        addressModel.validAddress(response);
                                    }
                                    addressValidationForm.fillValidateForm(self.validationContainer);
                                    if (addressModel.isDifferent() || addressModel.error() != null) {
                                        $('.validateAddressForm').show();
                                        self.openModal();
                                    } else {
                                        $(self.formSelector).off();
                                        $(self.formSelector).submit();
                                    }
                                    $('body').trigger('processStop');
                                });
                            } else {
                                $(self.formSelector).off();
                                $(self.formSelector).submit();
                            }
                        }
                    } catch (e) {
                        // If the address could not be validated for some reason, submit the form normally
                        $(self.formSelector).off();
                        $(self.formSelector).submit();
                    }
                }
            });
        },

        enableSubmit: function (form) {
            $(form).find("[type=submit]").prop("disabled", false);
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});
