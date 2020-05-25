/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([], function () {
    'use strict';

    var customsTitle = 'Duty',
        totalTaxTitle = 'Import Fees';

    return function (taxModule) {
        taxModule.prototype.hasCustomsTax = function () {
            return this.getDetails().some(function (detail) {
                return detail.rates.some(function (rate) {
                    return rate.title === customsTitle;
                });
            });
        };

        taxModule.prototype.getTotalTaxTitle = function () {
            return totalTaxTitle;
        };

        taxModule.prototype.getValueDetail = function () {
            var dutyTaxValue = 0;

            this.getDetails().some(function (detail) {
                if(detail.rates.some(function (rate) { return rate.title !== customsTitle; })) {
                    dutyTaxValue += detail.amount;
                }
            });

            return this.getFormattedPrice(dutyTaxValue);
        };

        taxModule.prototype.getTaxTitle = function (rate) {
            var percent = rate.percent;

            if(rate.title === customsTitle) {
                percent = null;
            }

            return rate.title + (percent !== null ? ' (' + percent + '%)' : '');
        };

        taxModule.prototype.getCustomTaxClass = function (rate) {
            if(rate.title === customsTitle) {
                return 'true';
            }

            return '';
        };

        return taxModule;
    };
});
