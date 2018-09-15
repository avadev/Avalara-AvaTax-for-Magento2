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
define(['Magento_Checkout/js/model/totals', 'certificatesSdk', 'jquery', 'mage/url'], function (totals, sdk, jQuery, urlBuilder) {
    'use strict';

    return function (targetModule) {

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
            var element = jQuery('<div class="avatax-certificate-dialog" />').appendTo('body');
            element.modal({buttons: []});
            element.modal('openModal');

            sdk(element[0], {
                //Customize colors of buttons
                primary_color: '#ff6600',
                secondary_color: '#ff6600',

                // Include if cert should be auto-approved
                submit_to_stack: false,

                // Include if cert is a renewal?
                upload_only: true,

                onCertSuccess: function () {
                    // Success callback
                }
            }).then(function () {
                var customer = {};
                customer.name = 'Customer';
                customer.address1 = '1300 EAST CENTRAL';
                customer.city = 'San Francisco';
                customer.state = 'California';
                customer.country = 'United States';
                customer.zip = '89890';

                GenCert.setCustomerData(customer);
                GenCert.setShipZone('California');
                GenCert.show();

                element.modal('openModal');
            })
        };

        return targetModule;
    };
});
