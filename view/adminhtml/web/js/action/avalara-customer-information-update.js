
define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();
        },

        /**
         * Update customer information at Avalara service
         */
        updateCustomerInformationAvalara: function () {
            if ("" !== this.customerUpdateUrl) {
                window.location.href = this.customerUpdateUrl;
            }
        }
    });
});
