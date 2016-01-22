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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var originalAddress = ko.observable(null);
        var validAddress = ko.observable(null);
        var selectedAddress = ko.observable(null);
        var error = ko.observable(null);
        var isDifferent = ko.observable(null);
        return {
            originalAddress: originalAddress,
            validAddress: validAddress,
            selectedAddress: selectedAddress,
            error: error,
            isDifferent: isDifferent,
            resetValues: function () {
                this.originalAddress(null);
                this.validAddress(null);
                this.selectedAddress(null);
                this.error(null);
                this.isDifferent(null);
            }
        };
    }
);
