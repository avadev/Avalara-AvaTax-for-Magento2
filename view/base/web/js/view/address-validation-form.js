define(
    [
        'jquery',
        'ko',
        'ClassyLlama_AvaTax/js/view/diff-address',
        'ClassyLlama_AvaTax/js/model/address-model',
        // This dependency will commonly already be loaded by Magento_Ui/js/core/app, however the load order is not
        // guaranteed, so we must require this dependency so that the custom Magento templateEngine is set before
        // ko.applyBindings is called in this file.
        'Magento_Ui/js/lib/ko/initialize'
    ],
    function (
        $,
        ko,
        diffAddress,
        addressModel
    ) {
        'use strict';

        return {
            validateAddressContainerSelector: '#validate_address',
            originalAddressTextSelector: ".original-address-text",
            validAddressTextSelector: ".valid-address-text",
            errorMessageContainerSelector: '.error-message-container',
            errorMessageTextSelector: '.error-message-text',
            addressOptionSelector: '.address-option',
            addressRadioGroupName: 'addressToUse',
            selectedAddressClass: 'selected',

            bindTemplate: function (containerSelector, config) {
                var template = $("<div class='" + this.bindingElement.replace('.', '') + "' data-bind=\"template: { name: 'ClassyLlama_AvaTax/baseValidateAddress', data: data }\"/>");

                function ViewModel() {
                    this.data = {
                        choice: config.hasChoice,
                        instructions: config.instructions,
                        errorInstructions: config.errorInstructions
                    }
                }

                ko.applyBindings(new ViewModel(), template.get(0));

                $(containerSelector).html(template);
            },

            fillValidateForm: function (form) {
                if (addressModel.error() != null) {
                    $(form).find(this.errorMessageContainerSelector).show();
                    $(form).find(this.errorMessageTextSelector).html(addressModel.error());
                    $(form).find('.yesError').show();
                    $(form).find('.noError').hide();
                    return;
                } else {
                    $(form).find('.yesError').hide();
                    $(form).find('.noError').show();
                    $(form).find(this.errorMessageContainerSelector).hide();
                }

                var originalAddress = this.buildOriginalAddress(addressModel.originalAddress());
                var validAddress = this.buildValidAddress(addressModel.originalAddress(), addressModel.validAddress());

                if (!diffAddress.isDifferent()) {
                    $(form).hide();
                    return;
                }

                var userCanChooseOriginalAddress = $(this.originalAddressTextSelector).length;

                if (userCanChooseOriginalAddress) {
                    // Original Address label
                    $(form).find(this.originalAddressTextSelector).html(originalAddress);
                    this.toggleRadioSelectedStyle(this.addressOptionSelector, this.addressRadioGroupName, this.selectedAddressClass);
                }

                $(form).find(this.validAddressTextSelector).html(validAddress);
            },

            buildValidAddress: function (originalAddress, validAddress) {
                var result = "";

                // Name
                result += originalAddress.firstname + " " + originalAddress.lastname + "<br/>";

                // Streets
                var maxStreets = 3;
                for (var i = 0; i < maxStreets; i++) {
                    var originalStreet = typeof originalAddress.street[i] === 'undefined' ? '' : originalAddress.street[i];
                    var validStreet = typeof validAddress.street[i] === 'undefined' ? '' : validAddress.street[i];
                    var validatedStreet = diffAddress.diffString(originalStreet, validStreet);
                    result += validatedStreet;
                    result += validatedStreet.length ? "<br/>" : "";
                }

                // City
                result += diffAddress.diffString(originalAddress.city, validAddress.city) + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    result += diffAddress.diffString(originalAddress.region_code, validAddress.region_code) + " ";
                } else {
                    result += diffAddress.diffString(originalAddress.region, validAddress.region) + " ";
                }

                // Postal code
                result += diffAddress.diffString(originalAddress.postcode, validAddress.postcode);

                return result;
            },

            buildOriginalAddress: function (originalAddress) {
                var result = "";

                // Name
                result += originalAddress.firstname + " " + originalAddress.lastname + "<br/>";

                // Streets
                $.each(originalAddress.street, function (index, value) {
                    if (value !== "") {
                        result += value + "<br/>";
                    }
                });

                // City
                result += originalAddress.city + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    result += originalAddress.region_code + " ";
                } else {
                    result += originalAddress.region + " ";
                }

                // Postal code
                result += originalAddress.postcode;

                return result;
            },

            /**
             * @param optionContainerSelector
             * @param radioGroupName
             * @param selectedClass
             */
            toggleRadioSelectedStyle: function (optionContainerSelector, radioGroupName, selectedClass) {
                $('input[name=' + radioGroupName + ']:radio').on('change', function () {
                    $(optionContainerSelector)
                        .removeClass(selectedClass)
                        .find('input[name=' + radioGroupName + ']:checked')
                        .parents(optionContainerSelector)
                        .addClass(selectedClass);
                });
            },

            setAddressToUse: function (hasChoice, form) {
                if (addressModel.error() == null) {
                    if (hasChoice) {
                        var selectedAddress = $(form + " input[type='radio']:checked").prop('id');
                        if (selectedAddress === 'validAddress') {
                            addressModel.selectedAddress(addressModel.validAddress());
                        } else {
                            addressModel.selectedAddress(addressModel.originalAddress());
                        }
                    } else {
                        addressModel.selectedAddress(addressModel.validAddress());
                    }
                } else {
                    addressModel.selectedAddress(addressModel.originalAddress());
                }
            },

            updateFormFields: function (form) {
                $(form).find("input[name*='street']").each(function (index) {
                    if (index < addressModel.selectedAddress().street.length) {
                        $(form).find("input[name*='street']").eq(index).val(addressModel.selectedAddress().street[index]);
                    } else {
                        $(form).find("input[name*='street']").eq(index).val("");
                    }
                });

                $(form).find("input[name*='region']").val(addressModel.selectedAddress().region);
                $(form).find("*:input[name*='region_id']").val(addressModel.selectedAddress().region_id);
                $(form).find("*:input[name*='country_id']").val(addressModel.selectedAddress().country_id);
                $(form).find("input[name*='postcode']").val(addressModel.selectedAddress().postcode);

            }
        }
    }
);