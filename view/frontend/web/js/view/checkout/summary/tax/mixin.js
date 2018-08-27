/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([], function () {
    'use strict';

    var customsTitle = 'Customs Duty and Import Tax';

    return function (taxModule) {
        var parentIfShowDetails = taxModule.prototype.ifShowDetails;

        taxModule.prototype.hasCustomsTax = function () {
            return this.getDetails().some(function (detail) {
                return detail.rates.some(function (rate) {
                    return rate.title === customsTitle;
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

        taxModule.prototype.getTaxTitle = function (rate) {
            var percent = rate.percent;

            if(rate.title === customsTitle) {
                percent = null;
            }

            return rate.title + (percent !== null ? ' (' + percent + '%)' : '');
        };

        return taxModule;
    };
});
