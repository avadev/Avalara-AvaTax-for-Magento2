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
        'ko',
        'mageUtils',
        'ClassyLlama_AvaTax/js/model/address-model'
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
                    $(form).find(this.addressValidationFormSelector).show();
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
                result += this.encodeHtml(originalAddress.firstname + " " + originalAddress.lastname) + "<br/>";

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

                var parent = this;

                // Name
                result += this.encodeHtml(originalAddress.firstname + " " + originalAddress.lastname) + "<br/>";

                // Streets
                $.each(originalAddress.street, function (index, value) {
                    if (value !== "") {
                        result += parent.encodeHtml(value) + "<br/>";
                    }
                });

                // City
                result += this.encodeHtml(originalAddress.city) + ", ";

                // State - The region_code isn't used for customer addresses
                if (typeof originalAddress.region_code !== 'undefined') {
                    result += this.encodeHtml(originalAddress.region_code) + " ";
                } else {
                    result += this.encodeHtml(originalAddress.region) + " ";
                }

                // Postal code
                result += this.encodeHtml(originalAddress.postcode);

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
                var field = 'street';
                $(form).find("input[name*=" + field + "]").each(function (index) {
                    var street;
                    if (index < addressModel.selectedAddress()[field].length) {
                        street = $(form).find("input[name*=" + field + "]").eq(index);
                    } else {
                        street = $(form).find("input[name*=" + field + "]").eq(index).attr('value', '');
                    }

                    if (street.val() !== addressModel.selectedAddress()[field][index]) {
                        $(street).attr('value', addressModel.selectedAddress()[field][index]).trigger('change');
                    }
                });

                this.updateFieldValue(form, 'city');
                this.updateFieldValue(form, 'region');
                this.updateFieldValue(form, 'region_id');
                this.updateFieldValue(form, 'country_id');
                this.updateFieldValue(form, 'postcode');
            },

            updateFieldValue: function (form, field) {
                var fieldElement = $(form).find("input[name*=" + field + "]");
                if (['country_id', 'region_id'].indexOf(field) > -1) {
                    fieldElement = $(form).find("select[name*=" + field + "]");
                }
                if (fieldElement.val() !== addressModel.selectedAddress()[field]) {
                    $(fieldElement).attr('value', addressModel.selectedAddress()[field]).trigger('change');
                }
            },

            diffAddressField: function (o, n) {
                o = this.encodeHtml(o);
                n = this.encodeHtml(n);
                if(o !== n) {
                    addressModel.isDifferent(true);
                    if (n.length) {
                        n = '<span class="address-field-changed">' + n + '</span>';
                    }
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
            },

            encodeHtml: function (str) {
                // This function will escape the contents of the provided string
                // Sourced from http://shebang.brandonmintern.com/foolproof-html-escaping-in-javascript/#the-best-way-to-escape-html-in-javascript
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }
        }
    }
);
