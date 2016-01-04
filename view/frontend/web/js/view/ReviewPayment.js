define(
    [
        'jquery',
        "underscore",
        'uiComponent',
        'ko',
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
                template: 'ClassyLlama_AvaTax/reviewPayment',
                paymentTemplate: 'Magento_Checkout/payment',
                validateTemplate: 'ClassyLlama_AvaTax/checkoutValidate',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),
            context: 'checkout',

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

            getValidateTemplate: function () {
                return this.validateTemplate;
            /**
             * Used in ReviewPayment.html to return the template path
             */
            }
        });
    }
);
