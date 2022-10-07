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

    // Grab a new token 5 minutes before the previous one expires to ensure our requests will have a valid token
    var expirationBuffer = 5 * 60 * 1000;
    var avaTaxTokenStorageKey = 'admin-avatax-token';
    var requiredInfo = [
        'token',
        'customer',
        'expires',
        'sdk_url'
    ];

    function generateTokenResolve(tokenInfo) {
        return jQuery.Deferred().resolve(tokenInfo.sdk_url, tokenInfo.token);
    }

    return function getSdkToken(tokenUrl, customerId) {
        var tokenInfo = window.localStorage.getItem(avaTaxTokenStorageKey);

        if(tokenInfo === null) {
            tokenInfo = '{}';
        }

        tokenInfo = JSON.parse(tokenInfo);

        if(tokenInfo === false) {
            tokenInfo = {};
        }

        if (tokenInfo[customerId] !== void(0) && tokenInfo[customerId].expires * 1000 > Date.now() + expirationBuffer) {
            return generateTokenResolve(tokenInfo[customerId]);
        }

        // Token has expired (or never existed), so remove it from storage
        delete tokenInfo[customerId];

        return jQuery.ajax({url: tokenUrl, type: 'post', data: {customer_id: customerId}}).then(
            function (response) {
                // If we don't have token info, return early
                if (!requiredInfo.every(function (key) {
                    return response.hasOwnProperty(key);
                })) {
                    return jQuery.Deferred().reject();
                }

                tokenInfo[customerId] = response;

                // Cache the token in local storage
                window.localStorage.setItem(avaTaxTokenStorageKey, JSON.stringify(tokenInfo));

                return generateTokenResolve(tokenInfo[customerId]);
            }
        );
    }
});
