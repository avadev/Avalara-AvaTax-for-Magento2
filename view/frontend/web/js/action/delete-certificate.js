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

define(['jquery', 'mage/storage'], function ($, storage) {
    'use strict';

    $('.delete-button').on('click', function() {
        deleteCertificate();
    });


    let deleteCertificate = function() {
        //todo get certificate id
        storage.delete('rest/V1/avatax/deletecertificate?certificateId=1').then(
            function(response) {
                //todo handle response
                console.log(response);
            }
        );
    }
});