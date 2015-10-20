<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Config
{
    // Connection Details
    const XML_PATH_AVATAX_CONNECTION_LIVE_MODE = 'tax/avatax_connection/live_mode';

    const XML_PATH_AVATAX_CONNECTION_ACCOUNT_NUMBER = 'tax/avatax_connection/account_number';

    const XML_PATH_AVATAX_CONNECTION_LICENSE_KEY = 'tax/avatax_connection/license_key';

    const XML_PATH_AVATAX_CONNECTION_COMPANY_CODE = 'tax/avatax_connection/company_code';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_ACCOUNT_NUMBER = 'tax/avatax_connection/development_account_number';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_LICENSE_KEY = 'tax/avatax_connection/development_license_key';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_COMPANY_CODE = 'tax/avatax_connection/development_company_code';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Live vs. Development mode of the module
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return bool
     */
    public function getLiveMode($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_LIVE_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get account number from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getAccountNumber($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_ACCOUNT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * get license key from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getLicenseKey($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_LICENSE_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get company code from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getCompanyCode($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_COMPANY_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get development account number from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getDevelopmentAccountNumber($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_ACCOUNT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get development license key from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getDevelopmentLicenseKey($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_LICENSE_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get development company code from config
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getDevelopmentCompanyCode($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_COMPANY_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}