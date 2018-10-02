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

define(['jquery'], function (jQuery) {
    'use strict';

    return function (stepNavigatorModule) {
        var parentNavigateTo = stepNavigatorModule.navigateTo;

        // Wrap the native navigate function so that we can trigger a jQuery event to listen to
        stepNavigatorModule.navigateTo = function navigateTo() {
            parentNavigateTo.apply(this, arguments);

            jQuery(window.document).trigger('checkout.navigateTo');
        };

        return stepNavigatorModule;
    };
});
