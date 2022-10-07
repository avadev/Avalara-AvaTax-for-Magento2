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
define(['jquery', 'mage/storage', 'Magento_Customer/js/model/customer'], function (jQuery, storage, customerModel) {
    'use strict';

    // Grab a new token 5 minutes before the previous one expires to ensure our requests will have a valid token
    var expirationBuffer = 5 * 60 * 1000;
    var avaTaxTokenStorageKey = 'avatax-token';
    var requiredInfo = [
        'token',
        'customer',
        'expires',
        'sdk_url'
    ];

    return function getSdkToken() {
        var tokenInfo = window.localStorage.getItem(avaTaxTokenStorageKey);

        if (tokenInfo !== null) {
            tokenInfo = JSON.parse(tokenInfo);

            // Ensure that tokens do not get reused across sessions
            if(!customerModel.isLoggedIn() || customerModel.customerData.id !== tokenInfo.customer_id) {
                tokenInfo = false;
            }

            if (tokenInfo !== false && tokenInfo.expires * 1000 > Date.now() + expirationBuffer) {
                return jQuery.Deferred().resolve(tokenInfo.sdk_url, tokenInfo.token);
            }

            window.localStorage.removeItem(avaTaxTokenStorageKey)
        }

        return storage.get('rest/V1/avatax/token').then(
            function (response) {
                // If we don't have token info, return early
                if (!requiredInfo.every(function (key) {return response.hasOwnProperty(key);})) {
                    return jQuery.Deferred().reject();
                }

                // Cache the token in local storage
                window.localStorage.setItem(avaTaxTokenStorageKey, JSON.stringify(response));

                return jQuery.Deferred().resolve(response.sdk_url, response.token, response.customer);
            }
        );
    }
});
