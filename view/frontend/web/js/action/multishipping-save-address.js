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
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'ClassyLlama_AvaTax/js/model/url-builder',
        'ClassyLlama_AvaTax/js/model/multishipping-save-address',
    ],
    function (
        $,
        storage,
        alert,
        urlBuilder,
        multishippingSaveAddressService,
    ) {
        'use strict';
        return function (address) {
            var serviceUrl = urlBuilder.createUrl('/multishipping/save-address', {}),

                payload = {
                    address: address,
                };

            return multishippingSaveAddressService(serviceUrl, payload);

        }
    }
);
