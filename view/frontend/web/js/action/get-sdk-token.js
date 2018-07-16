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
define(['jquery', 'mage/storage'], function (jQuery, storage) {
    'use strict';

    // Grab a new token 5 minutes before the previous one expires to ensure our requests will have a valid token
    var expirationBuffer = 5 * 60 * 1000;
    var avaTaxTokenStorageKey = 'avatax-token';

    return function getSdkToken() {
        var tokenInfo = window.localStorage.getItem(avaTaxTokenStorageKey);

        if (tokenInfo !== null) {
            tokenInfo = JSON.parse(tokenInfo);

            if(tokenInfo !== false && tokenInfo.expires * 1000 > Date.now() + expirationBuffer) {
                return jQuery.Deferred().resolve(tokenInfo.token);
            }

            window.localStorage.removeItem(avaTaxTokenStorageKey)
        }

        return storage.get('rest/V1/avatax/token').then(
            function (response) {
                var tokenInfo = void(0);

                // Only set the token info if we were returned the proper array response
                if(response instanceof Array) {
                    tokenInfo = response.pop();
                }

                // If we don't have token info, return early
                if(tokenInfo === void(0)) {
                    return false;
                }

                // Cache the token in local storage
                window.localStorage.setItem(avaTaxTokenStorageKey, JSON.stringify(tokenInfo));

                return jQuery.Deferred().resolve(tokenInfo.token);
            }
        );
    }
});
