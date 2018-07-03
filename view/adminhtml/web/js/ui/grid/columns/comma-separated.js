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
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ClassyLlama_AvaTax/ui/grid/cells/comma-separated'
        },

        hasValues: function(row) {
            return (row[this.index].length > 0);
        },

        /**
         * @param {Object} row - Data to be preprocessed
         * @returns {String}
         */
        getLabel: function(row) {
            return row[this.index].join(', ');
        },
    });

});