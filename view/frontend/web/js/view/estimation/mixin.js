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

        return estimationModule;
    };
});
