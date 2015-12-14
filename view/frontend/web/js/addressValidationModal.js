define([
    'jquery',
    'ko',
    'ClassyLlama_AvaTax/js/model/addressModel',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-converter',
    'ClassyLlama_AvaTax/js/view/customerValidationHandler',
    'Magento_Ui/js/modal/modal'
], function(
        $,
        ko,
        addressModel,
        setCustomerAddress,
        addressConverter,
        customerValidationHandler
    ){

    $.widget('ClassyLlama_AvaTax.addressValidationModal', $.mage.modal, {
        options: {
            title: 'Verify Your Address',
            modalClass: 'validationModal',
            responsive: true,
            trigger: '',
            modalLeftMargin: 45,
            closeText: $.mage.__('Close'),
            buttons: {}
        },
        validationContainer: '.validationModal .modal-content div',
        formSelector: '.form-address-edit',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();
            var self = this;


            var choice = this.options.hasChoice;
            var instructions = this.options.instructions;
            var errorInstructions = this.options.errorInstructions;
            var context = 'customer';
            $(this.validationContainer).html("<div data-bind=\"template:{" +
                "name: 'ClassyLlama_AvaTax/validate', " +
                "data: {" +
                "choice: " + choice + ", " +
                "instructions: '" + instructions + "', " +
                "errorInstructions: '" + errorInstructions + "', "  +
                "context: '" + context +
                "'}}\" />");

            var existingViewModel = ko.dataFor(document.body);
            ko.applyBindings(existingViewModel, $('#validate_address').get(0));

            $(this.formSelector).on('submit', function (e) {
                var isValid = $(':mage-validation').validation('isValid');
                if (isValid) {
                    e.preventDefault();
                    var addressObject = addressConverter.formAddressDataToCustomerAddress(self.serializeObject($(self.formSelector)));
                    addressModel.originalAddress(addressObject);
                    setCustomerAddress().done(function (response) {
                        customerValidationHandler.validationResponseHandler(response);
                    });


                    self.openModal();
                }
            });
        },
        /**
         * Open modal.
         * * @return {Element} - current element.
         */
        openModal: function () {
            this._super();
        },

        serializeObject: function (form) {
            var o = {};
            var a = form.serializeArray();
            $.each(a, function() {
                var name = this.name.replace(/\[|\]/g, "");
                if (o[name] !== undefined) {
                    if (!o[name].push) {
                        o[name] = [o[name]];
                    }
                    o[name].push(this.value || '');
                } else {
                    o[name] = this.value || '';
                }
            });

            return o;
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});