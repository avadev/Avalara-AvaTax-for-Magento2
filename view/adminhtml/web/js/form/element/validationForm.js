/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
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
