define([
    'jquery',
    'ko',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-converter',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
    'Magento_Ui/js/modal/modal'
], function(
        $,
        ko,
        addressModel,
        setCustomerAddress,
        addressConverter,
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
                        addressValidationForm.updateFormFields(this.formSelector);
                        this.closeModal();
                        $(this.formSelector).off('submit');
                        $(this.formSelector).submit();
                    }
                }
            ]
        },
        validationContainer: '.validationModal .modal-content > div',
        formSelector: '.form-address-edit',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();

            this.handleFormSubmit();
            addressValidationForm.bindTemplate(this.validationContainer, this.options, 'ClassyLlama_AvaTax/baseValidateAddress');
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " .edit-address").on('click', function () {
                self.closeModal();
            });
        },

        handleFormSubmit: function () {
            var self = this;
            $(this.formSelector).on('submit', function (e) {
                try {
                    $('.validateAddressForm').show();
                    var isValid = $(':mage-validation').validation('isValid');
                    if (isValid) {
                        e.preventDefault();
                        addressModel.error(null);
                        var addressObject = addressConverter.formAddressDataToCustomerAddress($(self.formSelector));
                        var inCountry = $.inArray(addressObject.countryId, self.options.countriesEnabled.split(',')) >= 0;
                        if (inCountry) {
                            addressModel.originalAddress(addressObject);
                            $('body').trigger('processStart');
                            setCustomerAddress().done(function (response) {
                                if (typeof response === 'string') {
                                    addressModel.error(response);
                                } else {
                                    addressModel.validAddress(response);
                                }
                                addressValidationForm.fillValidateForm(self.validationContainer);
                                if (addressModel.isDifferent() || addressModel.error() != null) {
                                    $('.validateAddressForm').show();
                                    self.openModal();
                                } else {
                                    $(self.formSelector).off();
                                    $(self.formSelector).submit();
                                }
                                $('body').trigger('processStop');
                            });
                        } else {
                            $(self.formSelector).off();
                            $(self.formSelector).submit();
                        }
                    }
                } catch (e) {
                    // If the address could not be validated for some reason, submit the form normally
                    $(self.formSelector).off();
                    $(self.formSelector).submit();
                }
            });
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});