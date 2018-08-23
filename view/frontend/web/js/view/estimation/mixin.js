/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
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
