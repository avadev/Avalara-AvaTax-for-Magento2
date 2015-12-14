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

                var originalAddress = addressModel.originalAddress();
                var validAddress = addressModel.validAddress();

                // Full Name
                var validResult = originalAddress.firstname + " " + originalAddress.lastname + "<br/>";
                var originalResult = validResult;

                // Streets
                for(var i = 0; i < 3; i++) {
                    var originalStreet = typeof originalAddress.street[i] === 'undefined'?'':originalAddress.street[i];
                    var validStreet = typeof validAddress.street[i] === 'undefined'?'':validAddress.street[i];
                    var validatedStreet = diffAddress.diffString(originalStreet, validStreet);
                    validResult += validatedStreet;
                    validResult += validatedStreet.length?"<br/>":"";
                }
                $.each(originalAddress.street, function(index, value) {
                    originalResult += value + "<br/>";
                });

                // City
                validResult += diffAddress.diffString(originalAddress.city, validAddress.city) + ", ";
                originalResult += originalAddress.city + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    validResult += diffAddress.diffString(originalAddress.region_code, validAddress.region_code) + " ";
                } else {
                    validResult += diffAddress.diffString(originalAddress.region, validAddress.region) + " ";
                }
                originalResult += originalAddress.region_code + " ";

                // ZIP code
                validResult += diffAddress.diffString(originalAddress.postcode, validAddress.postcode);
                originalResult += originalAddress.postcode;

                if (!diffAddress.isDifferent) {
                    $(this.validateAddressContainerSelector + ' *').hide();
                    return;
                }

                var userCanChooseOriginalAddress = $(this.originalAddressTextSelector).length;

                if (userCanChooseOriginalAddress) {
                    // Original Address label
                    $(this.validateAddressContainerSelector + " " + this.originalAddressTextSelector).html(originalResult);

                    this.toggleRadioSelectedStyle('.addressOption', 'addressToUse', 'selected');
                }

                $(this.validAddressTextSelector).html(validResult);
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