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
define(['jquery', 'uiElement', 'underscore', 'mage/translate', 'mage/url'], function (jQuery, Element, _, $t) {
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
         * @param {HTMLElement} idElement
         * @returns {Element}
         */
        initialize: function initialize(config, idElement) {
            this._super();

            _.bindAll(this, 'fetchAndUpdateCompanies', 'updateCompanyCodeFromCompanyId');

            this.idElement = idElement;
            this.accountNumberElement = document.getElementById(this.accountNumberId);
            this.licenseKeyElement = document.getElementById(this.licenseKeyId);
            this.companyCodeElement = document.getElementById(this.companyCodeId);

            if (this.accountNumberElement === null || this.licenseKeyElement === null || this.companyCodeElement === null) {
                return this;
            }

            // Watch for changes so we can provide instant company codes without the user needing to save the config
            this.accountNumberElement.addEventListener('change', this.fetchAndUpdateCompanies);
            this.licenseKeyElement.addEventListener('change', this.fetchAndUpdateCompanies);
            this.idElement.addEventListener('change', this.updateCompanyCodeFromCompanyId);

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
        getScope: function getScope() {
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
         * Set the company code hidden input based on the selected company
         */
        updateCompanyCodeFromCompanyId: function updateCompanyCodeFromCompanyId() {
            this.companyCodeElement.value = this.companyIdToCompanyCodeMap[this.idElement.item(this.idElement.selectedIndex).value];
        },

        /**
         * Build the company select, and select the currently saved company
         *
         * @param {Array} companies
         * @param {int} currentId
         */
        updateCompanyIds: function updateCompanyIds(companies, currentId) {
            this.idElement.innerHTML = '';

            this.idElement.add(new Option(companies.length > 0 ? $t('--Select a Company--') : $t('No available companies'), '', true, true));

            companies.forEach(function (company) {
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
         * Fetch company options and update the drop-down
         *
         * @returns {Deferred}
         */
        fetchAndUpdateCompanies: function fetchAndUpdateCompanies() {
            // If account number, license key, or url is null, we can't make any request
            if (this.url === null || this.accountNumberElement.value === '' || this.licenseKeyElement.value === '') {
                return jQuery.Deferred().reject();
            }

            var data = this.getScope();

            data['account_number'] = this.accountNumberElement.value;

            // If license key is obscured, don't send it and use the saved config value
            if (!RegExp("^[*]+$").test(this.licenseKeyElement.value)) {
                data['license_key'] = this.licenseKeyElement.value;
            }

            // We have to manually build the request and prevent native Magento's beforeSend handler,
            // otherwise the first request when the page loads doesn't work
            return jQuery.ajax({
                url: this.url,
                showLoader: true,
                type: 'post',
                data: data
            }).then((function (response) {
                this.updateCompanyIds(response.companies, response.current_id)
            }).bind(this));
        }
    });
});