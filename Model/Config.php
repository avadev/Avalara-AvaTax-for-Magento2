<?php

namespace ClassyLlama\AvaTax\Model;

use AvaTax\ATConfigFactory;
use ClassyLlama\AvaTax\Framework\AppInterface as AvaTaxAppInterface;
use Magento\Framework\AppInterface as MageAppInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\Information;
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

    // General Settings
    const XML_PATH_AVATAX_SETTINGS_CUSTOMER_CODE_FORMAT = 'tax/avatax_settings/customer_code_format';

    // Customer Code Format Options
    const CUSTOMER_FORMAT_OPTION_EMAIL = 'email';
    const CUSTOMER_FORMAT_OPTION_ID = 'id';
    const CUSTOMER_FORMAT_OPTION_NAME_ID = 'name_id';

    const CUSTOMER_FORMAT_NAME_ID = '%s (%s)';

    // Various Settings
    const API_URL_DEV = 'https://development.avalara.net';
    const API_URL_PROD = 'https://avatax.avalara.net';

    const API_PROFILE_NAME_DEV = 'Development';
    const API_PROFILE_NAME_PROD = 'Production';

    const API_APP_NAME_PREFIX = 'Magento 2';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var ProductMetadataInterface
     */
    protected $magentoProductMetadata = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $magentoProductMetadata,
        ATConfigFactory $avaTaxConfigFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->magentoProductMetadata = $magentoProductMetadata;
        $this->avaTaxConfigFactory = $avaTaxConfigFactory;
        $this->createAvaTaxProfile();
    }

    /**
     * Create a development profile and a production profile
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     */
    protected function createAvaTaxProfile()
    {
        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_DEV,
                'values' => [
                    'url'       => self::API_URL_DEV,
                    'account'   => $this->getDevelopmentAccountNumber(),
                    'license'   => $this->getDevelopmentLicenseKey(),
                    'trace'     => true,
                    'client' => $this->getClientName(),
                    'name' => self::API_PROFILE_NAME_DEV,
                ],
            ]
        );

        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_PROD,
                'values' => [
                    'url'       => self::API_URL_PROD,
                    'account'   => $this->getAccountNumber(),
                    'license'   => $this->getLicenseKey(),
                    'trace'     => false,
                    'client' => $this->getClientName(),
                    'name' => self::API_PROFILE_NAME_PROD,
                ],
            ]
        );
    }

    /**
     * Return origin address
     * TODO: Make sure all fields are of the appropriate type and if not, convert them
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return array
     */
    public function getOriginAddress($store = null)
    {
        return [
            'line1' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_STREET_LINE1,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'line2' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_STREET_LINE2,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'city' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_CITY,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'region' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_REGION_CODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'postalCode' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'country' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_COUNTRY_CODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
        ];
    }

    public function getCustomerCodeFormat($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SETTINGS_CUSTOMER_CODE_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Generate AvaTax Client Name from a combination of Magento version number and AvaTax module version number
     * Format: Magento 2.x Community - AvaTax 1.0.0
     * Limited to 50 characters to comply with API requirements
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return string
     */
    protected function getClientName()
    {
        return substr($this->magentoProductMetadata->getName(), 0, 7) . ' ' . // "Magento" - 8 chars
        substr($this->magentoProductMetadata->getVersion(), 0, 14) . ' ' . // 2.x & " " - 50 - 8 - 13 - 14 = 15 chars
        substr($this->magentoProductMetadata->getEdition(), 0, 10) . ' - ' . // "Community - "|"Enterprise - " - 13 chars
        'AvaTax ' . substr(AvaTaxAppInterface::APP_VERSION, 0, 7); // "AvaTax " & 1.x.x - 14 chars
    }

    /**
     * Get Vat Number
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return bool
     */
    public function getBusinessIdentificationNumber($store = null)
    {
        return $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_VAT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
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