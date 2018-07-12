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
define(['Magento_Checkout/js/model/totals'], function (totals) {
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

        return targetModule;
    };
});