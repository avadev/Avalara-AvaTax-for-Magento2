/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Customer/js/view/authentication-popup',
    'ClassyLlama_AvaTax/js/model/certificate-authentication-popup',
    'Magento_Customer/js/action/login',
    'jquery',
    'mage/url'
], function (CustomerAuthenticationPopup, authenticationPopupModel, loginAction, jQuery, url) {
    'use strict';

    return CustomerAuthenticationPopup.extend({
        defaults: {
            registerUrl: url.build('customer/account/create?redirect=checkout'),
            template: 'ClassyLlama_AvaTax/certificate-authentication-popup'
        },

        /** Init popup login window */
        setModalElement: function (element) {
            if (authenticationPopupModel.modalWindow == null) {
                authenticationPopupModel.createPopUp(element);
            }
        },

        login: function (formUiElement, event) {
            var loginData = {},
                formElement = jQuery(event.currentTarget),
                formDataArray = formElement.serializeArray();

            event.stopPropagation();
            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if (formElement.validation() &&
                formElement.validation('isValid')
            ) {
                this.isLoading(true);
                // BEGIN EDIT
                loginAction(loginData, url.build('checkout'));
                // END EDIT
            }

            return false;
        }
    });
});
