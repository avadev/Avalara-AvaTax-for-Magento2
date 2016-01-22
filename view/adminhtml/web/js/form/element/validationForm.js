define([
    'jquery',
    'Magento_UI/js/form/element/abstract'
], function (
    $,
    Abstract
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: ''
            },
            template: 'ClassyLlama_AvaTax/form/element/adminValidateAddress'
        },
        baseTemplate: 'ClassyLlama_AvaTax/baseValidateAddress',
        choice: 1,

        initialize: function () {
            this._super()
                .initFormId();
            $(document).on('click', '.validateAddressForm .instructions[data-uid="' + this.uid + '"] .edit-address', function () {
                $('html, body').animate({scrollTop: $("#container").offset().top}, 1000);
            });

            return this;
        },

        initFormId: function () {
            var namespace;

            if (this.formId) {
                return this;
            }

            namespace = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        getBaseValidateAddressTemplate: function () {
            return this.baseTemplate;
        }
    });
});
