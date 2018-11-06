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

define(['ClassyLlama_AvaTax/js/certificates-sdk'], function (certificatesSdk) {
    'use strict';

    return function (addExemptionComponent) {
        addExemptionComponent.prototype.renderSdk = function (element) {
            certificatesSdk(this.tokenUrl, this.customerId, element, this.sdkParameters).then(this.onSdkLoad);
        };

        return addExemptionComponent;
    };
});
