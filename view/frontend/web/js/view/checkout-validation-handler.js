define(
    [
        'jquery',
        'Magento_Checkout/js/model/step-navigator',
        'ClassyLlama_AvaTax/js/action/set-shipping-address',
        'ClassyLlama_AvaTax/js/view/update-address',
        'ClassyLlama_AvaTax/js/model/address-model',
        'ClassyLlama_AvaTax/js/validation-form',
        'ClassyLlama_AvaTax/js/diff-address'
    ],
    function (
        $,
        stepNavigator,
        setShippingAddress,
        updateAddress,
        addressModel,
        validationForm,
        diffAddress
    ) {
        'use strict';

        return {
            options: {
                validateAddressContainerSelector: '#validate_address'
            },

            validationResponseHandler: function (response) {
                diffAddress.isDifferent(false);
                if ((typeof response.extension_attributes !== 'undefined')
                    && (typeof response.extension_attributes.valid_address !== 'undefined'
                    && typeof response.extension_attributes.original_address !== 'undefined')
                ) {
                    $(this.options.validateAddressContainerSelector).fadeIn();
                    this.toggleAddressToUse();
                    updateAddress(response.extension_attributes.valid_address);
                    addressModel.originalAddress(response.extension_attributes.original_address);
                    addressModel.validAddress(response.extension_attributes.valid_address);
                    if (typeof response.extension_attributes.error_message !== 'undefined') {
                        addressModel.error(response.extension_attributes.error_message)
                    }
                    validationForm.fillValidateForm();

                    // This click event handler is to allow the user to navigate to the first step to change their
                    // address if they notice an error in their address on the Review & Payments step by clicking
                    // a link in the instructions above their address
                    $(this.options.validateAddressContainerSelector + ' .instructions a').on('click', function () {
                        stepNavigator.navigateTo('shipping', 'shipping');
                    });
                } else {
                    $(this.options.validateAddressContainerSelector).hide();
                }
            },


            toggleAddressToUse: function () {
                $('input[name=addressToUse]:radio').on('change', function() {
                    var validSelected = $('#validAddress:checked').length ? true : false;
                    setShippingAddress(validSelected);
                });
            }
        };
    }
);
