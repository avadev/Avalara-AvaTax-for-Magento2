define(
    [
        'jquery',
        'ClassyLlama_AvaTax/js/diffAddress',
        'ClassyLlama_AvaTax/js/model/addressModel'
    ],
    function (
        $,
        diffAddress,
        addressModel
    ) {
        'use strict';

        return {
            validateAddressContainerSelector: '#validate_address',
            originalAddressTextSelector: ".originalAddressText",
            validAddressTextSelector: ".validAddressText",
            errorMessageContainerSelector: '.errorMessageContainer',
            errorMessageTextSelector: '.errorMessageText',

            fillValidateForm: function () {
                if (addressModel.error() != null) {
                    $(this.errorMessageContainerSelector).show();
                    $(this.errorMessageTextSelector).html(addressModel.error());
                    $('.yesError').show();
                    $('.noError').hide();
                    return;
                } else {
                    $('.yesError').hide();
                    $('.noError').show();
                    $(this.errorMessageContainerSelector).hide();
                }

                var originalAddress = this.buildOriginalAddress(addressModel.originalAddress());
                var validAddress = this.buildValidAddress(addressModel.originalAddress(), addressModel.validAddress());

                if (!diffAddress.isDifferent) {
                    $(this.validateAddressContainerSelector + ' *').hide();
                    return;
                }

                var userCanChooseOriginalAddress = $(this.originalAddressTextSelector).length;

                if (userCanChooseOriginalAddress) {
                    // Original Address label
                    $(this.originalAddressTextSelector).html(originalAddress);
                    this.toggleRadioSelectedStyle('.addressOption', 'addressToUse', 'selected');
                }

                $(this.validAddressTextSelector).html(validAddress);
            },

            buildValidAddress: function (originalAddress, validAddress) {
                var result = "";

                // Name
                result += originalAddress.firstname + " " + originalAddress.lastname + "<br/>";

                // Streets
                for(var i = 0; i < 3; i++) {
                    var originalStreet = typeof originalAddress.street[i] === 'undefined'?'':originalAddress.street[i];
                    var validStreet = typeof validAddress.street[i] === 'undefined'?'':validAddress.street[i];
                    var validatedStreet = diffAddress.diffString(originalStreet, validStreet);
                    result += validatedStreet;
                    result += validatedStreet.length?"<br/>":"";
                }

                // City
                result += diffAddress.diffString(originalAddress.city, validAddress.city) + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    result += diffAddress.diffString(originalAddress.region_code, validAddress.region_code) + " ";
                } else {
                    result += diffAddress.diffString(originalAddress.region, validAddress.region) + " ";
                }

                // ZIP code
                result += diffAddress.diffString(originalAddress.postcode, validAddress.postcode);

                return result;
            },

            buildOriginalAddress: function (originalAddress) {
                var result = "";

                // Name
                result += originalAddress.firstname + " " + originalAddress.lastname + "<br/>";

                // Streets
                $.each(originalAddress.street, function(index, value) {
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

                // Zip code
                result += originalAddress.postcode;

                return result;
            },

            /**
             * @param optionContainerSelector
             * @param radioGroupName
             * @param selectedClass
             */
            toggleRadioSelectedStyle: function (optionContainerSelector, radioGroupName, selectedClass) {
                $('input[name=' + radioGroupName + ']:radio').on('change', function() {
                    $(optionContainerSelector)
                        .removeClass(selectedClass)
                        .find('input[name=' + radioGroupName + ']:checked')
                        .parents(optionContainerSelector)
                        .addClass(selectedClass);
                });
            }
        }
    }
);