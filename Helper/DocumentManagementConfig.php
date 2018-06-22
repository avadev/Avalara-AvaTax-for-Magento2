<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class DocumentManagementConfig extends AbstractHelper
{
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED = 'tax/avatax_document_management/enabled';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES = 'tax/avatax_document_management/enabled_countries';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ECOMMERCE_SDK_COMPANY_ID = 'tax/avatax_document_management/ecommerce_sdk_company_id';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ECOMMERCE_SDK_KEY = 'tax/avatax_document_management/ecommerce_sdk_key';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_no_certs_exist';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_certs_exist';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS = 'tax/avatax_document_management/checkout_link_text_manage_existing_certs';

    public function getEnabled( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED,
            $scopeType,
            $store
        );
    }

    public function getEnabledCountries( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES,
            $scopeType,
            $store
        );
    }

    public function getEcommerceSdkCompanyId( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ECOMMERCE_SDK_COMPANY_ID,
            $scopeType,
            $store
        );
    }

    public function getEcommerceSdkKey( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ECOMMERCE_SDK_KEY,
            $scopeType,
            $store
        );
    }

    public function getCheckoutLinkTextNewCertNoCertsExist( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST,
            $scopeType,
            $store
        );
    }

    public function getCheckoutLinkTextNewCertCertsExist( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST,
            $scopeType,
            $store
        );
    }

    public function getCheckoutLinkTextManageExistingCert( $store = null, $scopeType = ScopeInterface::SCOPE_STORE )
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS,
            $scopeType,
            $store
        );
    }

}