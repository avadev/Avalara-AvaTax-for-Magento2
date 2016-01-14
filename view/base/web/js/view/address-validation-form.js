define(
    [
        'jquery',
        'ko',
        'mageUtils',
        'ClassyLlama_AvaTax/js/model/address-model',
        // This dependency will commonly already be loaded by Magento_Ui/js/core/app, however the load order is not
        // guaranteed, so we must require this dependency so that the custom Magento templateEngine is set before
        // ko.applyBindings is called in this file.
        'Magento_Ui/js/lib/ko/initialize'
    ],
    function (
        $,
        ko,
        utils,
        addressModel
    ) {
        'use strict';

        return {
            validAddressRadioSelector: '.validAddress',
            originalAddressRadioSelector: '.originalAddress',
            originalAddressTextSelector: ".originalAddressText",
            validAddressTextSelector: ".validAddressText",
            errorMessageContainerSelector: '.errorMessageContainer',
            addressOptionSelector: '.addressOption',
            addressRadioGroupName: 'addressToUse',
            selectedAddressClass: 'selected',
            addressValidationFormSelector: '.validateAddressForm',
            validationForm: '#co-validate-form',

            bindTemplate: function (containerSelector, config, templateName) {
                var template = $("<div class='" + this.addressValidationFormSelector.replace('.', '') + "' data-bind=\"template: { name: '" + templateName + "', data: data }\"/>");

                function ViewModel() {
                    this.data = {
                        choice: config.hasChoice,
                        instructions: config.instructions,
                        errorInstructions: config.errorInstructions,
                        uid: utils.uniqueid()
                    }
                }

                ko.applyBindings(new ViewModel(), template.get(0));

                $(containerSelector).html(template);
            },

            fillValidateForm: function (form) {
                this.reset(form);

                if (addressModel.error() != null) {
                    $(form).find(this.errorMessageContainerSelector).show();
                    $(form).find(this.errorMessageContainerSelector + " .instructions .error-message").html(addressModel.error());
                    $(form).find(this.errorMessageContainerSelector + " " + this.originalAddressTextSelector).html(this.buildOriginalAddress(addressModel.originalAddress()));
                    $(form).find('.yesError').show();
                    $(form).find('.noError').hide();
                    $(form).show();
                    return;
                } else {
                    $(form).find('.yesError').hide();
                    $(form).find('.noError').show();
                    $(form).find(this.errorMessageContainerSelector).hide();
                }

                var originalAddress = this.buildOriginalAddress(addressModel.originalAddress());
                var validAddress = this.buildValidAddress(addressModel.originalAddress(), addressModel.validAddress());

                if (!addressModel.isDifferent()) {
                    $(form).find(this.addressValidationFormSelector).hide();
                    return;
                }

                var userCanChooseOriginalAddress = $(this.originalAddressTextSelector).length;
                if (userCanChooseOriginalAddress) {
                    $(form).find(this.originalAddressTextSelector).html(originalAddress);
                    this.toggleRadioSelected(form, this.addressRadioGroupName, this.selectedAddressClass);
                }

                $(form).find(this.validAddressTextSelector).html(validAddress);
                $(form).find(this.addressValidationFormSelector).show();
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
                    var validatedStreet = this.diffAddressField(originalStreet, validStreet);
                    result += validatedStreet;
                    result += validatedStreet.length ? "<br/>" : "";
                }

                // City
                result += this.diffAddressField(originalAddress.city, validAddress.city) + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    result += this.diffAddressField(originalAddress.region_code, validAddress.region_code) + " ";
                } else {
                    result += this.diffAddressField(originalAddress.region, validAddress.region) + " ";
                }

                // Postal code
                result += this.diffAddressField(originalAddress.postcode, validAddress.postcode);

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
             * @param form
             * @param radioGroupName
             * @param selectedClass
             */
            toggleRadioSelected: function (form, radioGroupName, selectedClass) {
                var self = this;
                $(form).find('input[name=' + radioGroupName + ']:radio').on('change', function () {
                    $(form).find(self.validationForm + " .selected")
                        .removeClass(selectedClass)
                        .parent()
                        .find('input[name=' + radioGroupName + ']:checked')
                        .parents(self.addressOptionSelector)
                        .addClass(selectedClass);

                    if ($(form).find(self.validAddressRadioSelector).is(':checked')) {
                        addressModel.selectedAddress(addressModel.validAddress());
                    } else {
                        addressModel.selectedAddress(addressModel.originalAddress());
                    }
                });
            },

            updateFormFields: function (form) {
                $(form).find("input[name*='street']").each(function (index) {
                    var street;
                    if (index < addressModel.selectedAddress().street.length) {
                        street = $(form).find("input[name*='street']").eq(index);
                    } else {
                        street = $(form).find("input[name*='street']").eq(index).attr('value', '');
                    }

                    if (street.val() !== addressModel.selectedAddress().street[index]) {
                        $(street).attr('value', addressModel.selectedAddress().street[index]).trigger('change');
                    }
                });

                var region = $(form).find("input[name*='region']");
                if (region.val() !== addressModel.selectedAddress().region) {
                    $(region).attr('value', addressModel.selectedAddress().region).trigger('change');
                }

                var region_id = $(form).find("*:input[name*='region_id']");
                if (region_id.val() !== addressModel.selectedAddress().region_id) {
                    $(region_id).attr('value', addressModel.selectedAddress().region_id).trigger('change');
                }

                var country_id = $(form).find("*:input[name*='country_id']");
                if (country_id.val() !== addressModel.selectedAddress().country_id) {
                    $(country_id).attr('value', addressModel.selectedAddress().country_id).trigger('change');
                }

                var postcode = $(form).find("input[name*='postcode']");
                if (postcode.val() !== addressModel.selectedAddress().postcode) {
                    $(postcode).attr('value', addressModel.selectedAddress().postcode).trigger('change');
                }
            },

            diffAddressField: function (o, n) {
                if(o !== n) {
                    addressModel.isDifferent(true);
                    n = '<span class="address-field-changed">' + n + '</span>';
                }
                return n;
            },

            reset: function (form) {
                // isDifferent() must be reset to false every time an address is validated or you could get a false
                // positive saying the address is different because it was in the last address that was validated
                addressModel.isDifferent(false);
                addressModel.selectedAddress(addressModel.validAddress());
                $(form).find(this.originalAddressRadioSelector).prop('checked', false);
                $(form).find(this.validAddressRadioSelector).prop('checked', true);
                $(form).find(this.originalAddressRadioSelector).parents(this.addressOptionSelector).removeClass(this.selectedAddressClass);
                $(form).find(this.validAddressRadioSelector).parents(this.addressOptionSelector).addClass(this.selectedAddressClass);
                $(form).find(this.addressValidationFormSelector).hide();
            }
        }
    }
);