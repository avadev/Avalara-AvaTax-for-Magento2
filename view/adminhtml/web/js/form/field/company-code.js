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

    return function companyCode(config, idElement) {
        var companyIdToCompanyCodeMap = [],
            url = config.url,
            accountNumberElement = document.getElementById(config.account_number_id),
            licenseKeyElement = document.getElementById(config.license_key_id),
            companyCodeElement = document.getElementById(config.company_code_id);

        if (accountNumberElement === null || licenseKeyElement === null || companyCodeElement === null) {
            return;
        }

        function updateCompanyCode() {
            companyCodeElement.value = companyIdToCompanyCodeMap[idElement.item(idElement.selectedIndex).value];
        }

        function updateCompanyId() {
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
                idElement.innerHTML = '';

                response.companies.forEach(function (company) {
                    companyIdToCompanyCodeMap[company.company_id] = company.company_code;
                    idElement.add(new Option(company.company_code + ' - ' + company.name, company.company_id, false, company.company_id === response.current_id));
                    updateCompanyCode();
                })
            });
        }

        if (accountNumberElement === null || licenseKeyElement === null || idElement === null) {
            return;
        }

        accountNumberElement.addEventListener('change', updateCompanyId);
        licenseKeyElement.addEventListener('change', updateCompanyId);
        idElement.addEventListener('change', updateCompanyCode);

        if (accountNumberElement.value !== null && licenseKeyElement.value !== null) {
            updateCompanyId();
        }
    }
});