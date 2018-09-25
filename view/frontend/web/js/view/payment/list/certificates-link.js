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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define([
    'Magento_Checkout/js/model/totals',
    'certificatesSdk',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/storage',
    'ClassyLlama_AvaTax/js/action/set-shipping-address'
], function (totals, sdk, jQuery, quote, getTotalsAction, fullScreenLoader, storage, setShippingAddress) {
    'use strict';

    return function (targetModule) {
        var parentInitialize = targetModule.prototype.initialize;

        // Inject into the initialize method so that we can subscribe to address changes
        targetModule.prototype.initialize = function initialize() {
            parentInitialize.apply(this, arguments);

            this.shippingToEnabledCountry = false;
            this.hasUploadedCertificate = false;
            this.observe(['shippingToEnabledCountry', 'hasUploadedCertificate']);

            if (this.enabledCountries === void(0)) {
                this.enabledCountries = [];
            }

            quote.shippingAddress.subscribe(function (address) {
                this.shippingToEnabledCountry(this.enabledCountries.indexOf(address.countryId) >= 0);
            }.bind(this));

            jQuery(window.document).on('checkout.navigateTo', function () {
                this.hasUploadedCertificate(false);
            }.bind(this));
        };

        targetModule.prototype.ifShowCertificateLink = function ifShowCertificateLink() {
            var amount = 0,
                taxTotal;

            if (totals) {
                taxTotal = totals.getSegment('tax');

                if (taxTotal) {
                    amount = taxTotal.value;
                }
            }

            return amount > 0;
        };

        targetModule.prototype.showNewCertificateModal = function showNewCertificateModal() {
            if (this.dialogElement !== void(0)) {
                this.dialogElement.remove();
            }

            var onCertificateComplete = (function () {
                this.hasUploadedCertificate(true);
                this.refreshTotals();
            }).bind(this);
            this.dialogElement = jQuery('<div class="avatax-certificate-dialog" />').appendTo('body');
            this.dialogElement.modal({buttons: []});
            this.dialogElement.modal('openModal');

            sdk(this.dialogElement[0], {
                // Include if cert is a renewal?
                upload: true,

                onCertSuccess: onCertificateComplete,
                onManualSubmit: onCertificateComplete,
                onUpload: onCertificateComplete
            }).then(function () {
                var address = quote.shippingAddress();

                GenCert.setShipZone(address.region);
                GenCert.show();

                this.dialogElement.modal('openModal');
            }.bind(this))
        };

        targetModule.prototype.refreshTotals = function refreshTotals() {
            GenCert.hide();
            this.dialogElement.modal('closeModal');
            fullScreenLoader.startLoader();

            return storage.get('/rest/V1/avatax/tax/refresh').then(function () {
                // Use this as a cheap way of being able to re-load taxes
                return setShippingAddress();
            }).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        };

        return targetModule;
    };
});
