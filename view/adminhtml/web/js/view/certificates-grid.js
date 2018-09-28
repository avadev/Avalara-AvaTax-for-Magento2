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
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function (
    $,
    Abstract,
    $t,
    confirm
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/view/certificates-grid',
            shouldShowWarning: false
        },

        handleDelete: function(deleteUrl) {
            confirm({
                title: $t('Delete Certificate'),
                content: $t('Are you sure you’d like to delete this certificate?'),
                actions: {
                    confirm: function() {
                        //make delete request.
                        window.location = deleteUrl;
                    }
                }
            });
        }
    });
});
