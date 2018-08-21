/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([], function () {
    'use strict';

    return function (taxModule) {
        var parentIfShowDetails = taxModule.prototype.ifShowDetails;

        taxModule.prototype.hasCustomsTax = function() {
            return this.getDetails().some(function (detail) {
                return detail.rates.some(function (rate) {
                    return rate.title === 'Customs Duty and Import Tax';
                });
            });
        };

        // Override the show details logic to force showing details if we have customs tax
        taxModule.prototype.ifShowDetails = function () {
            if (parentIfShowDetails.call(this)) {
                return true;
            }

            return this.hasCustomsTax();
        };

        return taxModule;
    };
});
