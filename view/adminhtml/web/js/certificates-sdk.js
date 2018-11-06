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

define(['ClassyLlama_AvaTax/js/action/get-sdk-token', 'ClassyLlama_AvaTax/js/load-sdk'], function (sdkToken, loadSdk) {
    return function (tokenUrl, customerId, container, params) {
        return sdkToken(tokenUrl, customerId).then(function (sdkUrl, token, customerId) {
            return loadSdk(container, params, sdkUrl, token, customerId);
        });
    }
});
