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
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/get-payment-information',
        'ClassyLlama_AvaTax/js/action/set-valid-address',
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
        addressConverter,
        getPaymentInformation,
        setValidAddress,
        checkoutDataResolver
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'ClassyLlama_AvaTax/ReviewPayment',
                paymentTemplate: 'Magento_Checkout/payment',
                validateTemplate: 'ClassyLlama_AvaTax/validate',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),
            validateAddressContainerSelector: '#validate',
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
                });
                console.log('Navigated to payment');
                setValidAddress().done(function (response) {
                    self.fillValidateForm(response);
                    self.isVisible(true);
                });
            },

            getFormKey: function() {
                return window.checkoutConfig.formKey;
            },

            getPaymentTemplate: function () {
                return this.paymentTemplate;
            },

            getValidateTemplate: function () {
                return this.validateTemplate;
            },

            fillValidateForm: function (validAddress) {
                if(typeof validAddress === 'string') {
                    return false;
                }
                var originalAddress = quote.shippingAddress();
                var validAddress = addressConverter.formAddressDataToQuoteAddress(validAddress);

                // Full Name
                var validResult = originalAddress.firstname + " " + originalAddress.lastname + "<br/>";
                var originalResult = validResult;

                // Streets
                for(var i = 0; i < 3; i++) {
                    var originalStreet = typeof originalAddress.street[i] === 'undefined'?'':originalAddress.street[i];
                    var validStreet = typeof validAddress.street[i] === 'undefined'?'':validAddress.street[i];
                    var validatedStreet = this.diffString(originalStreet, validStreet);
                    validResult += validatedStreet;
                    validResult += validatedStreet.length?"<br/>":"";
                }
                $.each(originalAddress.street, function(index, value) {
                    originalResult += value + "<br/>";
                });

                // City
                validResult += this.diffString(originalAddress.city, validAddress.city) + ", ";
                originalResult += originalAddress.city + ", ";

                // State
                validResult += this.diffString(originalAddress.regionCode, validAddress.regionCode) + " ";
                originalResult += originalAddress.regionCode + " ";

                // ZIP code
                validResult += this.diffString(originalAddress.postcode, validAddress.postcode);
                originalResult += originalAddress.postcode;

                if (this.choice === 1) {
                    // Original Address label
                    $(this.validateAddressContainerSelector + " label:eq(0)").html(originalResult);

                    // Valid Address label
                    $(this.validateAddressContainerSelector + " label:eq(1)").html(validResult);

                    this.toggleRadioSelectedStyle('.addressOption', 'addressToUse', 'selected');
                } else {
                    $(".validatedAddress").html(validResult);
                }
            },

            sanitizePostcode: function (validPostcode, originalPostcode) {
                validPostcode = $(validPostcode);
                var resultPostcode = "";
                $.each(validPostcode, function (index, value) {
                    if (value.innerHTML.split('-')[0].trim() === originalPostcode.trim()) {
                        if (value.tagName === "DEL") {
                            resultPostcode += "";
                        } else if (value.tagName === "INS" && value.innerHTML.indexOf('-') == 5) {
                            resultPostcode += originalPostcode.trim() + "<ins>-" + value.innerHTML.split('-')[1].trim() + "</ins>";
                        } else {
                            resultPostcode += value.outerHTML;
                        }
                    } else {
                        resultPostcode += value.outerHTML;
                    }
                });
                return resultPostcode;
            },

            /**
             * @param optionContainerSelector
             * @param radioGroupName
             * @param selectedClass
             */
            toggleRadioSelectedStyle: function (optionContainerSelector, radioGroupName, selectedClass) {
                $('input[name=' + radioGroupName + ']:radio').on('change', function() {
                    $(optionContainerSelector)
                        .removeClass(selectedClass)
                        .find('input[name=' + radioGroupName + ']:checked')
                        .parents(optionContainerSelector)
                        .addClass(selectedClass);
                });
            },

            /**
             * Javascript Diff Algorithm
             *  By John Resig (http://ejohn.org/)
             *  Modified by Nathan Toombs
             *
             * Released under the MIT license.
             *
             * More Info:
             *  http://ejohn.org/projects/javascript-diff-algorithm/
             * @param o
             * @param n
             * @returns {string}
             */
            diffString: function ( o, n ) {
                o = o.replace(/\s+$/, '');
                n = n.replace(/\s+$/, '');

                var out = this.diff(o == "" ? [] : o.replace(/([-.])/, ' $1').split(/\s+/), n == "" ? [] : n.replace(/([-.])/, ' $1').split(/\s+/));
                var str = "";

                var oSpace = o.match(/\s+/g);
                if (oSpace == null) {
                    oSpace = [""];
                } else {
                    oSpace.push("");
                }
                var nSpace = n.match(/\s+/g);
                if (nSpace == null) {
                    nSpace = [""];
                } else {
                    nSpace.push("");
                }

                if (out.n.length == 0) {
                    for (var i = 0; i < out.o.length; i++) {
                        str += '<del>' + out.o[i] + "</del>" + oSpace[i];
                    }
                } else {
                    if (out.n[0].text == null) {
                        for (n = 0; n < out.o.length && out.o[n].text == null; n++) {
                            str += '<del>' + out.o[n] + "</del>" + oSpace[n];
                        }
                    }

                    for ( var i = 0; i < out.n.length; i++ ) {
                        if (out.n[i].text == null) {
                            str += '<ins>' + out.n[i] + "</ins>" + (typeof nSpace[i] === 'undefined'?'':nSpace[i]);
                        } else {
                            var pre = "";

                            for (n = out.n[i].row + 1; n < out.o.length && out.o[n].text == null; n++ ) {
                                pre += '<del>' + out.o[n] + "</del>" + oSpace[n];
                            }
                            str += out.n[i].text + nSpace[i] + pre;
                        }
                    }
                }

                return str.trim();
            },

            diff: function ( o, n ) {
                var ns = {};
                var os = {};

                for ( var i = 0; i < n.length; i++ ) {
                    if ( ns[ n[i] ] == null )
                        ns[ n[i] ] = { rows: [], o: null };
                    ns[ n[i] ].rows.push( i );
                }

                for ( var i = 0; i < o.length; i++ ) {
                    if ( os[ o[i] ] == null )
                        os[ o[i] ] = { rows: [], n: null };
                    os[ o[i] ].rows.push( i );
                }

                for ( var i in ns ) {
                    if ( ns[i].rows.length == 1 && typeof(os[i]) != "undefined" && os[i].rows.length == 1 ) {
                        n[ ns[i].rows[0] ] = { text: n[ ns[i].rows[0] ], row: os[i].rows[0] };
                        o[ os[i].rows[0] ] = { text: o[ os[i].rows[0] ], row: ns[i].rows[0] };
                    }
                }

                for ( var i = 0; i < n.length - 1; i++ ) {
                    if ( n[i].text != null && n[i+1].text == null && n[i].row + 1 < o.length && o[ n[i].row + 1 ].text == null &&
                        n[i+1] == o[ n[i].row + 1 ] ) {
                        n[i+1] = { text: n[i+1], row: n[i].row + 1 };
                        o[n[i].row+1] = { text: o[n[i].row+1], row: i + 1 };
                    }
                }

                for ( var i = n.length - 1; i > 0; i-- ) {
                    if ( n[i].text != null && n[i-1].text == null && n[i].row > 0 && o[ n[i].row - 1 ].text == null &&
                        n[i-1] == o[ n[i].row - 1 ] ) {
                        n[i-1] = { text: n[i-1], row: n[i].row - 1 };
                        o[n[i].row-1] = { text: o[n[i].row-1], row: i - 1 };
                    }
                }

                return { o: o, n: n };
            }
        });
    }
);
