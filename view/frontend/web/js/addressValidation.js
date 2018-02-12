/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */
define([
    'jquery',
    'jquery/ui',
    'validation'
], function ($) {
    'use strict';

    $.widget('mage.addressValidation', {
        options: {
            selectors: {
                button: '[data-action=save-address]'
            }
        },

        /**
         * Validation creation
         * @protected
         */
        _create: function () {
            var button = $(this.options.selectors.button, this.element);

            this.element.validation({

                /**
                 * Submit Handler
                 * @param {Element} form - address form
                 */
                submitHandler: function (form) {

                    button.attr('disabled', true);
                    // BEGIN EDIT - Add conditional to form submit
                    if (!$(form).data('avataxAddressValidationEnabled')) {
                        // Store is not using AvaTax address validation, submit form natively
                        form.submit();
                    }
                    // END EDIT
                }
            });
        }
    });

    return $.mage.addressValidation;
});
