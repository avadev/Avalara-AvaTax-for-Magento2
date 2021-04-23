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
define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/view/address-validation-form',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/model/address-converter',
        'ClassyLlama_AvaTax/js/action/set-billing-address',
        'Magento_Checkout/js/model/quote',
        'ClassyLlama_AvaTax/js/view/checkout-billing-address-validation-modal',
    ],
    function ($, addressValidationForm, addressModel, addressConverter, setBillingAddress, quote, addressValidationModal) {
        'use strict';
        return {
            modal: false,
            options: window.checkoutConfig.billingAddressValidation,
            validationContainer: '.billingValidationModal .modal-content > div',

            validate: function () {
                $('body').trigger('processStart');
                var self = this, isValid;

                if (this.options.validationEnabled &&
                    (typeof this.options.isAddressValid == "undefined" || this.options.isAddressValid === false)
                ) {
                    isValid = self.validateBillingAddress();
                } else {
                    isValid = true;
                }
                $('body').trigger('processStop');
                return isValid;
            },
            validateBillingAddress: function () {
                var isValid = false,
                    self = this,
                    addressObject = addressConverter.formAddressDataToCustomerAddress(quote.billingAddress()),
                    inCountry = $.inArray(addressObject.countryId, self.options.countriesEnabled.split(',')) >= 0;
                addressModel.error(null);

                if (inCountry) {
                    if (!self.modal) {
                        self.modal = addressValidationModal(self.options);
                    }
                    $('.validateAddressForm').show();
                    addressObject = self.cleanUnAddressObject(addressObject);
                    addressModel.originalAddress(addressObject);
                    addressModel.error(null);
                    setBillingAddress().done(function (response) {
                        if (typeof response === 'string') {
                            addressModel.error(response);
                        } else {
                            addressModel.validAddress(response);
                        }
                        addressValidationForm.fillValidateForm(self.validationContainer);
                        if (addressModel.isDifferent() || addressModel.error() != null) {
                            isValid = false;
                            self.modal.openModal();
                            $('.validateAddressForm').show();
                        } else {
                            isValid = true;
                        }
                    });
                    return isValid;
                }
            },
            cleanUnAddressObject: function (address) {
                var allowedAddressProperties = [
                    "customerId",
                    "countryId",
                    "region",
                    "regionId",
                    "regionCode",
                    "street",
                    "company",
                    "telephone",
                    "fax",
                    "postcode",
                    "city",
                    "firstname",
                    "lastname",
                    "middlename",
                    "prefix",
                    "suffix",
                    "vatId",
                ];

                var addressKeys = Object.keys(address);
                for (var i = 0; i < addressKeys.length; i++) {
                    if (allowedAddressProperties.indexOf(addressKeys[i]) < 0) {
                        delete address[addressKeys[i]];
                    }
                }

                return address;
            }

        }
    }
);
