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
    'ko',
    'ClassyLlama_AvaTax/js/model/address-model',
    'ClassyLlama_AvaTax/js/view/address-validation-form',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/model/quote'
], function (
    $,
    ko,
    addressModel,
    addressValidationForm,
    checkoutDataResolver,
    selectBillingAddress,
    createBillingAddress,
    quote
) {

    $.widget('ClassyLlama_AvaTax.checkoutBillingAddressValidationModal', $.mage.modal, {
        options: {
            title: $.mage.__('Verify Your Address'),
            modalClass: 'billingValidationModal',
            focus: '.billingValidationModal .action-primary',
            responsive: true,
            closeText: $.mage.__('Close'),
            buttons: [
                {
                    text: $.mage.__('Edit Address'),
                    class: 'action-secondary action-dismiss',
                    click: function () {
                        var paymentMethod = quote.paymentMethod().method;
                        $(`input[value=${paymentMethod}]`).parents('.payment-method').find('.action-edit-address').trigger('click');
                        window.checkoutConfig.billingAddressValidation.isAddressValid = false;
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Save Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        if (addressModel.isDifferent()) {
                            selectBillingAddress(createBillingAddress(addressModel.selectedAddress()));
                            checkoutDataResolver.applyBillingAddress();
                            addressValidationForm.updateFormFields(this.formSelector);
                        }
                        window.checkoutConfig.billingAddressValidation.isAddressValid = true;
                        this.closeModal();
                    }
                }
            ]
        },
        validationContainer: '.billingValidationModal .modal-content > div',
        formSelector: '.billing-address-form form',

        _create: function () {
            this._super();
            addressValidationForm.bindTemplate(this.validationContainer, this.options, 'ClassyLlama_AvaTax/baseValidateAddress');
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " .edit-address").on('click', function () {
                var paymentMethod = quote.paymentMethod().method;
                $(`input[value=${paymentMethod}]`).parents('.payment-method').find('.action-edit-address').trigger('click');
                window.checkoutConfig.billingAddressValidation.isAddressValid = false;
                self.closeModal();
            });
        },

        closeModal: function () {
            this._super();
        },

    });

    return $.ClassyLlama_AvaTax.checkoutBillingAddressValidationModal;
});
