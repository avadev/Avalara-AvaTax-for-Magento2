
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/form/address/element/adminValidateAddress'
        },
        baseTemplate: 'ClassyLlama_AvaTax/form/address/element/baseValidateAddress',
        choice: 1,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            $(document).on('click', '.avataxValidateAddressForm .instructions[data-uid="' + this.uid + '"] .edit-address', function () {
                $('.modal-inner-wrap').animate({scrollTop: $('.modal-slide').offset().top}, 1000);
            });
            return this;
        },

        /**
         * @returns {string}
         */
        getBaseValidateAddressTemplate: function () {
            return this.baseTemplate;
        }
    });
});
