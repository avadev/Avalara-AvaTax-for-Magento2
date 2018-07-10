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
define(['jquery', 'uiElement', 'mage/url'], function (jQuery, Element) {
    return Element.extend({
        defaults: {
            url: null,
            companyIdToCompanyCodeMap: [],
            accountNumberId: null,
            licenseKeyId: null,
            companyCodeId: null
        },

        /**
         * Initialize component
         *
         * @param {Object} config
         * @param {DOMElement} idElement
         * @returns {Element}
         */
        initialize: function(config, idElement) {

            this._super();

            this.idElement = idElement;
            this.accountNumberElement = document.getElementById(this.accountNumberId);
            this.licenseKeyElement = document.getElementById(this.licenseKeyId);
            this.companyCodeElement = document.getElementById(this.companyCodeId);

            if (this.accountNumberElement === null || this.licenseKeyElement === null || this.companyCodeElement === null) {
                return;
            }

            // Watch for changes so we can provide instant company codes without the user needing to save the config
            this.accountNumberElement.addEventListener('change', this.fetchAndUpdateCompanies.bind(this));
            this.licenseKeyElement.addEventListener('change', this.fetchAndUpdateCompanies.bind(this));
            this.idElement.addEventListener('change', this.updateCompanyCodeFromCompanyId.bind(this));

            // If we already have values for credentials, fetch company ids
            if (this.accountNumberElement.value !== null && this.licenseKeyElement.value !== null) {
                this.fetchAndUpdateCompanies();
            }

            return this;
        },

        /**
         * Returns the scope from the form action to determine how to load the save config settings
         *
         * @returns {Object}
         */
        getScope: function() {
            var formScope = document.getElementById('config-edit-form').action.match(/section\/\w+\/(website|store)\/(\d+)/i);

            if (formScope === null) {
                return {
                    scope_type: 'global'
                };
            }

            return {
                scope: formScope[2],
                scope_type: formScope[1]
            };
        },

        /**
         * Fetch company options and update the drop-down
         */
        fetchAndUpdateCompanies: function() {
            this.fetchCompanies(
                this.accountNumberElement.value,
                this.licenseKeyElement.value
            ).then((function (response) {
                this.updateCompanyIds(response.companies, response.current_id)
            }).bind(this));
        },

        /**
         * Set the company code hidden input based on the selected company
         */
        updateCompanyCodeFromCompanyId: function() {
            this.companyCodeElement.value = this.companyIdToCompanyCodeMap[this.idElement.item(this.idElement.selectedIndex).value];
        },

        /**
         * Build the company select, and select the currently saved company
         *
         * @param {Array} companies
         * @param {int} currentId
         */
        updateCompanyIds: function(companies, currentId) {
            this.idElement.innerHTML = '';

            companies.forEach(function(company) {
                var companyNameDisplay = company.name;

                if (company.company_code !== null) {
                    companyNameDisplay = company.company_code + ' - ' + companyNameDisplay;
                }

                this.companyIdToCompanyCodeMap[company.company_id] = company.company_code;
                this.idElement.add(new Option(companyNameDisplay, company.company_id, false, company.company_id === currentId));
                this.updateCompanyCodeFromCompanyId();
            }.bind(this))
        },

        /**
         * Grab the companies from the API
         *
         * @param {string} accountNumber
         * @param {string} licenseKey
         * @returns {Object}
         */
        fetchCompanies: function(accountNumber, licenseKey) {
            if (this.url == null) {
                return;
            }

            // If either account number or license key is null, we can't make any request
            if (accountNumber === '' || licenseKey === '') {
                return jQuery.Deferred().reject();
            }

            var data = this.getScope();

            data['account_number'] = accountNumber;

            // If license key is obscured, don't send it and use the saved config value
            if (!RegExp("^[*]+$").test(licenseKey)) {
                data['license_key'] = licenseKey;
            }

            // We have to manually build the request and prevent native Magento's beforeSend handler,
            // otherwise the first request when the page loads doesn't work
            return jQuery.ajax({
                url: this.url,
                showLoader: true,
                type: 'post',
                data: data
            });
        }
    });
});