/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'Magento_UI/js/form/element/abstract'
], function (
    $,
    utils,
    Abstract
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: ''
            },
            template: 'ClassyLlama_AvaTax/form/element/button'
        },

        addressComponentSelector: '.address-item-edit',
        formSelector: '.address-item-edit-content fieldset',

        /**
         * Initializes file component.
         *
         * @returns {Media} Chainable.
         */
        initialize: function () {
            this._super()
                .initFormId();

            return this;
        },

        /**
         * Defines form ID with which file input will be associated.
         *
         * @returns {Media} Chainable.
         */
        initFormId: function () {
            var namespace;

            if (this.formId) {
                return this;
            }

            namespace   = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        validateAddress: function () {
            var addressObject = this.serializeForm($(this.addressComponentSelector).closest(this.formSelector));
            console.log(addressObject);
        },

        serializeForm: function (form) {
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
});
