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
    ['jquery'],
    function($) {
        return {
            method: "rest",
            storeCode: window.avaTaxStoreCode,
            version: 'V1',
            serviceUrl: ':method/:storeCode/:version',

            createUrl: function(url, params) {
                var completeUrl = this.serviceUrl + url;
                return this.bindParams(completeUrl, params);
            },
            bindParams: function(url, params) {
                params.method = this.method;
                params.storeCode = this.storeCode;
                params.version = this.version;

                var urlParts = url.split("/");
                urlParts = urlParts.filter(Boolean);

                $.each(urlParts, function(key, part) {
                    part = part.replace(':', '');
                    if (params[part] != undefined) {
                        urlParts[key] = params[part];
                    }
                });
                return urlParts.join('/');
            }
        };
    }
);
