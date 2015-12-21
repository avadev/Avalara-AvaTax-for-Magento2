define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'ClassyLlama_AvaTax/js/action/set-valid-address',
        'ClassyLlama_AvaTax/js/view/updateAddress',
        'ClassyLlama_AvaTax/js/model/addressModel'
    ],
    function (
        $,
        ko,
        quote,
        stepNavigator,
        setValidAddress,
        updateAddress,
        addressModel
    ) {
        'use strict';

        return {
            validateAddressContainerSelector: '#validate',
            originalAddressInputSelector: "#originalAddress",
            validAddressInputSelector: "#validAddress",
            originalAddressTextSelector: ".originalAddressText",
            validAddressTextSelector: ".validAddressText",
            errorMessageContainerSelector: '.errorMessageContainer',
            errorMessageTextSelector: '.errorMessageText',
            isDifferent: false,

            shippingInformationResponseHandeler: function (response) {
                if (typeof response.extension_attributes !== 'undefined') {
                    $('#validate *').fadeIn();
                    this.fillValidateForm(response.extension_attributes);
                    //originalAddress.originalAddress(quote.shippingAddress());
                    updateAddress(response.extension_attributes.valid_address);
                    addressModel.originalAddress(response.extension_attributes.original_address);
                    addressModel.validAddress(response.extension_attributes.valid_address);

                    $('#validate .instructions a').on('click', function () {
                        stepNavigator.navigateTo('shipping', 'shipping');
                    });
                } else {
                    $('#validate *').hide();
                }
            },

            fillValidateForm: function (shippingAddress) {
                if (typeof shippingAddress['error_message'] !== 'undefined') {
                    $(this.errorMessageContainerSelector).show();
                    $(this.errorMessageTextSelector).html(shippingAddress['error_message']);
                    $('.yesError').show();
                    $('.noError').hide();
                    return;
                } else {
                    $('.yesError').hide();
                    $('.noError').show();
                    $(this.errorMessageContainerSelector).hide();
                }

                var originalAddress = shippingAddress['original_address'];
                var validAddress = shippingAddress['valid_address'];

                // This may be needed if testing discovers anything wrong with the validAddress fields so for now
                // I'm leaving this here
                //var validAddress = addressConverter.formAddressDataToQuoteAddress(shippingAddress);

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
                validResult += this.diffString(originalAddress.region_code, validAddress.region_code) + " ";
                originalResult += originalAddress.region_code + " ";

                // ZIP code
                validResult += this.diffString(originalAddress.postcode, validAddress.postcode);
                originalResult += originalAddress.postcode;

                if (!this.isDifferent) {
                    $('#validate *').hide();
                    return;
                }

                var userCanChooseOriginalAddress = $(this.originalAddressTextSelector).length;

                if (userCanChooseOriginalAddress) {
                    // Original Address label
                    $(this.validateAddressContainerSelector + " " + this.originalAddressTextSelector).html(originalResult);

                    this.toggleRadioSelectedStyle('.addressOption', 'addressToUse', 'selected');
                }

                $(this.validAddressTextSelector).html(validResult);
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
                    var validSelected = $('#validAddress:checked').length ? true : false;
                    setValidAddress(validSelected);
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
                            str += '<ins>' + out.n[i] + "</ins>" + nSpace[i];
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

                if (JSON.stringify(o) !== JSON.stringify(n)) {
                    this.isDifferent = true;
                }

                return { o: o, n: n };
            }
        };
    }
);
