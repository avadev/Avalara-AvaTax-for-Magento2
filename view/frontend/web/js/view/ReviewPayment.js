define(
    [
        'jquery',
        "underscore",
        'uiComponent',
        'ko',
        'mageUtils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (
        $,
        _,
        Component,
        ko,
        utils,
        quote,
        stepNavigator,
        paymentService,
        methodConverter,
        getPaymentInformation,
        checkoutDataResolver
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'ClassyLlama_AvaTax/ReviewPayment',
                paymentTemplate: 'Magento_Checkout/payment',
                checkoutValidateAddressTemplate: 'ClassyLlama_AvaTax/checkoutValidateAddress',
                baseValidateAddressTemplate: 'ClassyLlama_AvaTax/baseValidateAddress',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),
            context: 'checkout',
            uid: utils.uniqueid(),

            initialize: function () {
                this._super();
                checkoutDataResolver.resolvePaymentMethod();
                stepNavigator.registerStep(
                    'payment',
                    null,
                    'Review & Payments',
                    this.isVisible,
                    _.bind(this.navigate, this),
                    20
                );
                return this;
            },

            navigate: function () {
                var self = this;
                getPaymentInformation().done(function () {
                    self.isVisible(true);
                    $('#validate_address').hide();
                });
            },

            getFormKey: function() {
                return window.checkoutConfig.formKey;
            },

            /**
             * Used in ReviewPayment.html to return the template path
             */
            getPaymentTemplate: function () {
                return this.paymentTemplate;
            },

            /**
             * Used in ReviewPayment.html to return the template path
             */
            getCheckoutValidateAddressTemplate: function () {
                return this.checkoutValidateAddressTemplate;
            },

            /**
             * Used in checkoutValidateAddress.html to return the template path
             */
            getBaseValidateAddressTemplate: function () {
                return this.baseValidateAddressTemplate;
            }
        });
    }
);
