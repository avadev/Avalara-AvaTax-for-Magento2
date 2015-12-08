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
            trigger: '.form-address-edit .submit',
            modalLeftMargin: 45,
            closeText: $.mage.__('Close'),
            buttons: {}
        },
        /**
         * Creates modal widget.
         */
        _create: function () {
            this._super();
            console.log('modal open');
        }
    });

    return $.ClassyLlama_AvaTax.addressValidationModal;
});