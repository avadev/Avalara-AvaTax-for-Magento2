define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal' // usually widget can be found in /lib/web/mage dir
], function($){

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
        formSelector: '.form-address-edit',
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();
            var self = this;
            $(this.formSelector).on('submit', function (e) {
                var isValid = $(':mage-validation').validation('isValid');
                if (isValid) {
                    e.preventDefault();
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
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});