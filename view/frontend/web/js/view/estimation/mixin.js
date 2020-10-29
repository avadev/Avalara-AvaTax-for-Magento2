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

    return function (estimationModule) {
        estimationModule.prototype.getDetails = function() {
            var taxSegment = totals.getSegment('tax');

            if (taxSegment && taxSegment['extension_attributes']) {
                return taxSegment['extension_attributes']['tax_grandtotal_details'];
            }

            return [];
        };

        estimationModule.prototype.hasAvaTaxMessages = function () {
            var totalsExtensionAttributes = totals.totals().extension_attributes;

            return totalsExtensionAttributes !== void(0) && totalsExtensionAttributes.avatax_messages !== void(0) && totalsExtensionAttributes.avatax_messages.length > 0;
        };

        return estimationModule;
    };
});
