/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define(['ClassyLlama_AvaTax/js/certificates-sdk'], function (certificatesSdk) {
    'use strict';

    return function (addExemptionComponent) {
        addExemptionComponent.prototype.renderSdk = function (element) {
            certificatesSdk(element, this.sdkParameters).then(this.onSdkLoad);
        };

        return addExemptionComponent;
    };
});
