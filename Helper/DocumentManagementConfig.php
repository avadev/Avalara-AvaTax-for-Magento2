<?php
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

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class DocumentManagementConfig extends AbstractHelper
{
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED = 'tax/avatax_document_management/enabled';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES = 'tax/avatax_document_management/enabled_countries';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_no_certs_exist';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_certs_exist';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS = 'tax/avatax_document_management/checkout_link_text_manage_existing_certs';

	const XML_PATH_CERTCAPTURE_AUTO_VALIDATION = 'tax/avatax_certificate_capture/disable_certcapture_auto_validation';
	
    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return bool
     */
    public function isEnabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED,
            $scopeType,
            $store
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return array
     */
    public function getEnabledCountries($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $enabledCountries = $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES,
            $scopeType,
            $store
        );

        return array_filter(explode(',', $enabledCountries));
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getCheckoutLinkTextNewCertNoCertsExist($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST,
            $scopeType,
            $store
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getCheckoutLinkTextNewCertCertsExist($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST,
            $scopeType,
            $store
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getCheckoutLinkTextManageExistingCert($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS,
            $scopeType,
            $store
        );
    }
	
	/**
     * @return bool
     */
    public function isCertificatesAutoValidationDisabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CERTCAPTURE_AUTO_VALIDATION,
            $scopeType,
            $store
        );
    }


}
