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
        'Magento_Checkout/js/model/step-navigator',
        'ClassyLlama_AvaTax/js/action/set-shipping-address',
        'ClassyLlama_AvaTax/js/view/update-address',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/view/address-validation-form'
    ],
    function (
        $,
        stepNavigator,
        setShippingAddress,
        updateAddress,
        addressModel,
        addressValidationForm
    ) {
        'use strict';

        return {
            options: {
                validateAddressContainerSelector: '#validate_address'
            },
            validAddressRadioSelector: '.validAddress',
            addressValidationRadioGroupName: 'addressToUse',

            validationResponseHandler: function (response) {
                addressModel.error(null);
                if (typeof response.extension_attributes !== 'undefined') {
                    $(this.options.validateAddressContainerSelector + ' *').fadeIn();
                    this.toggleAddressToUse();
                    if (typeof response.extension_attributes.valid_address !== 'undefined') {
                        updateAddress(response.extension_attributes.valid_address, true);
                        addressModel.validAddress(response.extension_attributes.valid_address);
                    }
                    addressModel.originalAddress(response.extension_attributes.original_address);
                    if (typeof response.extension_attributes.error_message !== 'undefined') {
                        addressModel.error(response.extension_attributes.error_message)
                    }
                    addressValidationForm.fillValidateForm(this.options.validateAddressContainerSelector);
                    if (!addressModel.isDifferent() && addressModel.error() == null) {
                        $(this.options.validateAddressContainerSelector + " *").hide();
                    }
                    // This click event handler is to allow the user to navigate to the first step to change their
                    // address if they notice an error in their address on the Review & Payments step by clicking
                    // a link in the instructions above their address
                    $(this.options.validateAddressContainerSelector + ' .instructions .edit-address').on('click', function () {
                        stepNavigator.navigateTo('shipping', 'shipping');
                    });
                } else {
                    $(this.options.validateAddressContainerSelector + " *").hide();
                }
            },

            toggleAddressToUse: function () {
                var self = this;
                // This function is called every time an initial address validation request is made which could happen
                // multiple times so the change event binding is removed to prevent multiple api requests being sent
                // when a user selects between the original and valid address
                $('input[name=' + this.addressValidationRadioGroupName + ']:radio').off('change');
                $('input[name=' + this.addressValidationRadioGroupName + ']:radio').on('change', function() {
                    var validSelected = $(self.validAddressRadioSelector).is(':checked');
                    setShippingAddress(validSelected);
                });
            }
        };
    }
);
