define(['jquery', 'uiElement', 'mage/url'], function (jQuery, Element) {
    return Element.extend({
        defaults: {
            companyIdToCompanyCodeMap: [],
            account_number_id: null,
            license_key_id: null,
            company_code_id: null
        },

        // Returns the scope from the form action to determine how to load the save config settings
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

        initialize: function initialize(config, idElement) {
            var fetchAndUpdateCompanies = (function () {
                this.fetchCompanies(this.accountNumberElement.value, this.licenseKeyElement.value)
                    .then((function onCompanyFetch(response) {
                        this.updateCompanyIds(response.companies, response.current_id)
                    }).bind(this));
            }).bind(this);

            this._super();

            this.idElement = idElement;
            this.accountNumberElement = document.getElementById(this.account_number_id);
            this.licenseKeyElement = document.getElementById(this.license_key_id);
            this.companyCodeElement = document.getElementById(this.company_code_id);

            if (this.accountNumberElement === null || this.licenseKeyElement === null || this.companyCodeElement === null) {
                return;
            }

            // Watch for changes so we can provide instant company codes without the user needing to save the config
            this.accountNumberElement.addEventListener('change', fetchAndUpdateCompanies);
            this.licenseKeyElement.addEventListener('change', fetchAndUpdateCompanies);
            this.idElement.addEventListener('change', this.updateCompanyCodeFromCompanyId.bind(this));

            // If we already have values for credentials, fetch company ids
            if (this.accountNumberElement.value !== null && this.licenseKeyElement.value !== null) {
                fetchAndUpdateCompanies();
            }
        },

        // Set the company code hidden input based on the selected company
        updateCompanyCodeFromCompanyId: function updateCompanyCodeFromCompanyId() {
            this.companyCodeElement.value = this.companyIdToCompanyCodeMap[this.idElement.item(this.idElement.selectedIndex).value];
        },

        // Build the company select, and select the currently saved company
        updateCompanyIds: function updateCompanyIds(companies, currentId) {
            this.idElement.innerHTML = '';

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

        // Grab the companies from the API
        fetchCompanies: function fetchCompanies(accountNumber, licenseKey) {
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
                data: data,
                beforeSend: function () {
                }
            });
        }
    });
});