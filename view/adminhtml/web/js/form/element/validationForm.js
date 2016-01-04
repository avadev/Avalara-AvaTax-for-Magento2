/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'Magento_UI/js/form/element/abstract',
    'Magento_Ui/js/modal/alert',
    'ClassyLlama_AvaTax/js/action/set-customer-address',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
    'ClassyLlama_AvaTax/js/lib/serialize-form'
], function (
    $,
    utils,
    Abstract,
    alert,
    setCustomerAddress,
    addressModel,
    addressValidationForm
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: ''
            },
            template: 'ClassyLlama_AvaTax/form/element/customerValidateAddress'
        },
        baseTemplate: 'ClassyLlama_AvaTax/baseValidateAddress',

        /**
         * Initializes file component.
         *
         * @returns {Media} Chainable.
         */
        initialize: function () {
            this._super()
                .initFormId();

            // TODO: Add binding to <a> in instructions to focus on street input when clicked

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

        getBaseValidateAddressTemplate: function () {
            return this.baseTemplate;
        },

        setOriginalAddress: function () {
            addressValidationForm.setOriginalAddress();
        },

        setValidAddress: function () {
            addressValidationForm.setValidAddress();
        }
    });
});
