define([
    'jquery',
    'ko',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-converter',
    'ClassyLlama_AvaTax/js/view/customer-validation-handler',
    'ClassyLlama_AvaTax/js/diff-address',
    'ClassyLlama_AvaTax/js/validation-form',
    'Magento_Ui/js/modal/modal',
    // This dependency will commonly already be loaded by Magento_Ui/js/core/app, however the load order is not
    // guaranteed, so we must require this dependency so that the custom Magento templateEngine is set before
    // ko.applyBindings is called in this file.
    'Magento_Ui/js/lib/ko/initialize'
], function(
        $,
        ko,
        addressModel,
        setCustomerAddress,
        addressConverter,
        customerValidationHandler,
        diffAddress,
        validationForm
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
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Save Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        this.setAddressToUse();
                        this.updateFormFields();
                        this.closeModal();
                        $(this.formSelector).off('submit');
                        $(this.formSelector).submit();
                    }
                }
            ]
        },
        addressToUse: null,
        validationContainer: '.validationModal .modal-content > div',
        bindingElement: '.validate-binding',
        formSelector: '.form-address-edit',
        validationForm: '#co-validate-form',
        errorInstructionSelector: '.errorMessageContainer .instructions',
        originalAddressContainer: '.errorMessageContainer .originalAddressText',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();

            this.handleFormSubmit();
            this.bindTemplateToModal();
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " a").on('click', function () {
                self.closeModal();
            });
        },

        bindTemplateToModal: function () {
            var self = this;
            var template = $("<div class='" + this.bindingElement.replace('.', '') + "' data-bind=\"template: { name: 'ClassyLlama_AvaTax/customerValidate', data: data }\"/>");
            function ViewModel() {
                this.data = {
                    choice: self.options.hasChoice,
                    instructions: self.options.instructions,
                    errorInstructions: self.options.errorInstructions
                }
            }

            ko.applyBindings(new ViewModel(), template.get(0));

            $(this.validationContainer).html(template);
        },

        handleFormSubmit: function () {
            var self = this;
            $(this.formSelector).on('submit', function (e) {
                var isValid = $(':mage-validation').validation('isValid');
                if (isValid) {
                    e.preventDefault();
                    var addressObject = addressConverter.formAddressDataToCustomerAddress($(self.formSelector));
                    var inCountry = $.inArray(addressObject.countryId, self.options.countriesEnabled.split(',')) >= 0;
                    if (inCountry) {
                        addressModel.originalAddress(addressObject);
                        $(self.bindingElement).trigger('processStart');
                        setCustomerAddress().done(function (response) {
                            customerValidationHandler.validationResponseHandler(response);
                            if (addressModel.error() != null) {
                                var errorInstructions = self.options.errorInstructions;
                                $(self.errorInstructionSelector).html(errorInstructions.replace('%s', addressModel.error()));
                                $(self.originalAddressContainer).html(validationForm.buildOriginalAddress(addressModel.originalAddress()));
                                addressModel.error(null);
                                self.openModal();
                            } else if (diffAddress.isDifferent()) {
                                self.openModal();
                            } else {
                                $(self.formSelector).off();
                                $(self.formSelector).submit();
                            }
                        });
                    } else {
                        $(self.formSelector).off();
                        $(self.formSelector).submit();
                    }
                }
            });
        },

        setAddressToUse: function () {
            if (this.options.hasChoice) {
                var selectedAddress = $(this.validationForm + " input[type='radio']:checked").prop('id');
                if (selectedAddress === 'validAddress') {
                    this.addressToUse = addressModel.validAddress();
                } else {
                    this.addressToUse = addressModel.originalAddress();
                }
            } else {
                this.addressToUse = addressModel.validAddress();
            }
        },

        updateFormFields: function () {
            var self = this;
            $(this.formSelector + " *:input[name^='street']").each(function (index) {
                if (index < self.addressToUse.street.length) {
                    $(self.formSelector + " *:input[name^='street']").eq(index).val(self.addressToUse.street[index]);
                } else {
                    $(self.formSelector + " *:input[name^='street']").eq(index).val("");
                }
            });

            $(this.formSelector + " *:input[name^='region']").val(self.addressToUse.region);
            $(this.formSelector + " *:input[name^='region_id']").val(self.addressToUse.region_id);
            $(this.formSelector + " *:input[name^='country_id']").val(self.addressToUse.country_id);
            $(this.formSelector + " *:input[name^='postcode']").val(self.addressToUse.postcode);
            $(this.formSelector + " *:input[name^='city']").val(self.addressToUse.city);
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});