define(['jquery', 'mage/url'], function (jQuery) {
    function getScope() {
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
    }

    return function companyCode(config, companyCodeElement) {
        var companyCodeToAccountNumberMap = [],
            url = config.url,
            accountNumberElement = document.getElementById(config.account_number_id),
            licenseKeyElement = document.getElementById(config.license_key_id),
            companyIdElement = document.getElementById(config.company_id_id);

        if (accountNumberElement === null || licenseKeyElement === null || companyIdElement === null) {
            return;
        }

        function updateCompanyId() {
            companyIdElement.value = companyCodeToAccountNumberMap[companyCodeElement.item(companyCodeElement.selectedIndex).value];
        }

        function updateCompanyCode() {
            // If either account number or license key is null, we can't make any request
            if (accountNumberElement.value === '' || licenseKeyElement.value === '') {
                return jQuery.Deferred().reject();
            }

            var data = getScope();

            data['account_number'] = accountNumberElement.value;

            // If license key is obscured, don't send it and use the saved config value
            if (!RegExp("^[*]+$").test(licenseKeyElement.value)) {
                data['license_key'] = licenseKeyElement.value;
            }

            // We have to manually build the request and prevent native Magento's beforeSend handler,
            // otherwise the first request when the page loads doesn't work
            jQuery.ajax({
                url: url,
                showLoader: true,
                type: 'post',
                data: data,
                beforeSend: function () {
                }
            }).then(function (response) {
                companyCodeElement.innerHTML = '';

                response.companies.forEach(function (company) {
                    companyCodeToAccountNumberMap[company.company_code] = company.account_id;
                    companyCodeElement.add(new Option(company.name, company.company_code, false, company.company_code === response.current_code));
                    updateCompanyId();
                })
            });
        }

        if (accountNumberElement === null || licenseKeyElement === null || companyCodeElement === null) {
            return;
        }

        accountNumberElement.addEventListener('change', updateCompanyCode);
        licenseKeyElement.addEventListener('change', updateCompanyCode);
        companyCodeElement.addEventListener('change', updateCompanyId);

        if (accountNumberElement.value !== null && licenseKeyElement.value !== null) {
            updateCompanyCode();
        }
    }
});