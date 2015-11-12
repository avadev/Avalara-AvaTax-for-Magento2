<?php

namespace ClassyLlama\AvaTax\Model;

use AvaTax\ATConfigFactory;
use ClassyLlama\AvaTax\Framework\AppInterface as AvaTaxAppInterface;
use Magento\Framework\AppInterface as MageAppInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Phrase;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Config
{
    /**#@+
     * Module config settings
     */
    const XML_PATH_AVATAX_MODULE_ENABLED = 'tax/avatax/enabled';

    const XML_PATH_AVATAX_LIVE_MODE = 'tax/avatax/live_mode';

    const XML_PATH_AVATAX_PRODUCTION_ACCOUNT_NUMBER = 'tax/avatax/production_account_number';

    const XML_PATH_AVATAX_PRODUCTION_LICENSE_KEY = 'tax/avatax/production_license_key';

    const XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE = 'tax/avatax/production_company_code';

    const XML_PATH_AVATAX_DEVELOPMENT_ACCOUNT_NUMBER = 'tax/avatax/development_account_number';

    const XML_PATH_AVATAX_DEVELOPMENT_LICENSE_KEY = 'tax/avatax/development_license_key';

    const XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE = 'tax/avatax/development_company_code';

    const XML_PATH_AVATAX_CUSTOMER_CODE_FORMAT = 'tax/avatax/customer_code_format';

    const XML_PATH_AVATAX_USE_VAT = 'tax/avatax/use_business_identification_number';

    const XML_PATH_AVATAX_REF1 = 'tax/avatax/ref1';

    const XML_PATH_AVATAX_REF2 = 'tax/avatax/ref2';
    /**#@-*/

    /**#@+
     * Constants for shipping origin.
     *
     * These constants are missing from \Magento\Shipping\Model\Config. If they get added to the core in the future,
     * refactor this code to use the core constants.
     */
    // TODO: Check status of this issue to see if we can reference core constants in the future: https://github.com/magento/magento2/issues/2269
    const XML_PATH_SHIPPING_ORIGIN_STREET_LINE1 = 'shipping/origin/street_line1';

    const XML_PATH_SHIPPING_ORIGIN_STREET_LINE2 = 'shipping/origin/street_line2';
    /**#@-*/

    /**#@+
     * Customer Code Format Options
     */
    const CUSTOMER_FORMAT_OPTION_EMAIL = 'email';

    const CUSTOMER_FORMAT_OPTION_ID = 'id';

    const CUSTOMER_FORMAT_OPTION_NAME_ID = 'name_id';
    /**#@-*/

    /**
     * Customer Code Format for "name_id" option
     */
    const CUSTOMER_FORMAT_NAME_ID = '%s (%s)';

    /**#@+
     * AvaTax API values
     */
    const API_URL_DEV = 'https://development.avalara.net';

    const API_URL_PROD = 'https://avatax.avalara.net';

    const API_PROFILE_NAME_DEV = 'Development';

    const API_PROFILE_NAME_PROD = 'Production';
    /**#@-*/

    /**
     * Magento version prefix
     */
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
     * Return whether module is enabled
     *
     * @param null $store
     * @return mixed
     */
    public function isModuleEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_MODULE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return origin address
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return array
     */
    public function getOriginAddress($store = null)
    {
        return [
            'line1' => $this->scopeConfig->getValue(
                self::XML_PATH_SHIPPING_ORIGIN_STREET_LINE1,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'line2' => $this->scopeConfig->getValue(
                self::XML_PATH_SHIPPING_ORIGIN_STREET_LINE2,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'city' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_CITY,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'regionId' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_REGION_ID,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'postalCode' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'country' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
        ];
    }

    /**
     * Get Customer code format to pass to AvaTax API
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return mixed
     */
    public function getCustomerCodeFormat($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CUSTOMER_CODE_FORMAT,
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
            self::XML_PATH_AVATAX_LIVE_MODE,
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
            self::XML_PATH_AVATAX_PRODUCTION_ACCOUNT_NUMBER,
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
            self::XML_PATH_AVATAX_PRODUCTION_LICENSE_KEY,
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
            self::XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE,
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
            self::XML_PATH_AVATAX_DEVELOPMENT_ACCOUNT_NUMBER,
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
            self::XML_PATH_AVATAX_DEVELOPMENT_LICENSE_KEY,
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
            self::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get whether should use Business Identification Number (VAT)
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getUseBusinessIdentificationNumber($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_USE_VAT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get ref1 configured attribute code
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getRef1($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_REF1,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get ref2 configured attribute code
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getRef2($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_REF2,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}