define([
    'jquery',
    'ko',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-converter',
    'ClassyLlama_AvaTax/js/view/customer-validation-handler',
    'ClassyLlama_AvaTax/js/view/diff-address',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
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
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Save Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        addressValidationForm.setAddressToUse(this.options.hasChoice, this.validationForm);
                        addressValidationForm.updateFormFields(this.formSelector);
                        this.closeModal();
                        $(this.formSelector).off('submit');
                        $(this.formSelector).submit();
                    }
                }
            ]
        },
        addressToUse: null,
        validationContainer: '.validationModal .modal-content > div',
        formSelector: '.form-address-edit',
        validationForm: '#co-validate-form',
        errorInstructionSelector: '.errorMessageContainer .instructions',
        originalAddressContainer: '.errorCessageContainer .originalAddressText',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();

            this.handleFormSubmit();
            addressValidationForm.bindTemplate(this.validationContainer, this.options);
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " a").on('click', function () {
                self.closeModal();
            });
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
                        $("." + self.options.modalClass).trigger('processStart');
                        setCustomerAddress().done(function (response) {
                            customerValidationHandler.validationResponseHandler(response);
                            if (addressModel.error() != null) {
                                var errorInstructions = self.options.errorInstructions;
                                $(self.errorInstructionSelector).html(errorInstructions.replace('%s', addressModel.error()));
                                $(self.originalAddressContainer).html(addressValidationForm.buildOriginalAddress(addressModel.originalAddress()));
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
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});