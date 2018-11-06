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
