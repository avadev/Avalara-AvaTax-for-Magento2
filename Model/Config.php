<?php

namespace ClassyLlama\AvaTax\Model;

use AvaTax\ATConfigFactory;
use ClassyLlama\AvaTax\Framework\AppInterface as AvaTaxAppInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Phrase;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\State;
use Magento\Tax\Api\TaxClassRepositoryInterface;

/**
 * AvaTax Config model
 */
class Config
{
    /**#@+
     * Module config settings
     */
    const XML_PATH_AVATAX_MODULE_ENABLED = 'tax/avatax/enabled';

    const XML_PATH_AVATAX_TAX_MODE = 'tax/avatax/tax_mode';

    const XML_PATH_AVATAX_COMMIT_SUBMITTED_TRANSACTIONS = 'tax/avatax/commit_submitted_transactions';

    const XML_PATH_AVATAX_LIVE_MODE = 'tax/avatax/live_mode';

    const XML_PATH_AVATAX_PRODUCTION_ACCOUNT_NUMBER = 'tax/avatax/production_account_number';

    const XML_PATH_AVATAX_PRODUCTION_LICENSE_KEY = 'tax/avatax/production_license_key';

    const XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE = 'tax/avatax/production_company_code';

    const XML_PATH_AVATAX_DEVELOPMENT_ACCOUNT_NUMBER = 'tax/avatax/development_account_number';

    const XML_PATH_AVATAX_DEVELOPMENT_LICENSE_KEY = 'tax/avatax/development_license_key';

    const XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE = 'tax/avatax/development_company_code';

    const XML_PATH_AVATAX_CUSTOMER_CODE_FORMAT = 'tax/avatax/customer_code_format';

    const XML_PATH_AVATAX_SKU_SHIPPING = 'tax/avatax/sku_shipping';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_ORDER = 'tax/avatax/sku_gift_wrap_order';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_ITEM = 'tax/avatax/sku_gift_wrap_item';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_CARD = 'tax/avatax/sku_gift_wrap_card';

    const XML_PATH_AVATAX_SKU_ADJUSTMENT_POSITIVE = 'tax/avatax/sku_adjustment_positive';

    const XML_PATH_AVATAX_SKU_ADJUSTMENT_NEGATIVE = 'tax/avatax/sku_adjustment_negative';

    const XML_PATH_AVATAX_SKU_LOCATION_CODE = 'tax/avatax/location_code';

    const XML_PATH_AVATAX_REF1 = 'tax/avatax/ref1';

    const XML_PATH_AVATAX_REF2 = 'tax/avatax/ref2';

    const XML_PATH_AVATAX_USE_VAT = 'tax/avatax/use_business_identification_number';

    const XML_PATH_AVATAX_ERROR_ACTION = 'tax/avatax/error_action';

    const XML_PATH_AVATAX_ERROR_ACTION_DISABLE_CHECKOUT_MESSAGE_FRONTEND = 'tax/avatax/error_action_disable_checkout_message_frontend';

    const XML_PATH_AVATAX_ERROR_ACTION_DISABLE_CHECKOUT_MESSAGE_BACKEND = 'tax/avatax/error_action_disable_checkout_message_backend';

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_ENABLED = "tax/avatax/address_validation_enabled";

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_METHOD = "tax/avatax/address_validation_user_has_choice";

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_COUNTRIES_ENABLED = "tax/avatax/address_validation_countries_enabled";

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITH_CHOICE = "tax/avatax/address_validation_instructions_with_choice";

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITHOUT_CHOICE = "tax/avatax/address_validation_instructions_without_choice";

    const XML_PATH_AVATAX_ADDRESS_VALIDATION_ERROR_INSTRUCTIONS = "tax/avatax/address_validation_error_instructions";

    const XML_PATH_AVATAX_LOG_DB_LEVEL = 'tax/avatax/logging_db_level';

    const XML_PATH_AVATAX_LOG_DB_DETAIL = 'tax/avatax/logging_db_detail';

    const XML_PATH_AVATAX_LOG_DB_LIFETIME = 'tax/avatax/logging_db_lifetime';

    const XML_PATH_AVATAX_LOG_FILE_ENABLED = 'tax/avatax/logging_file_enabled';

    const XML_PATH_AVATAX_LOG_FILE_MODE = 'tax/avatax/logging_file_mode';

    const XML_PATH_AVATAX_LOG_FILE_LEVEL = 'tax/avatax/logging_file_level';

    const XML_PATH_AVATAX_LOG_FILE_DETAIL = 'tax/avatax/logging_file_detail';

    const XML_PATH_AVATAX_QUEUE_MAX_RETRY_ATTEMPTS = 'tax/avatax/queue_max_retry_attempts';

    const XML_PATH_AVATAX_QUEUE_COMPLETE_LIFETIME = 'tax/avatax/queue_complete_lifetime';

    const XML_PATH_AVATAX_QUEUE_FAILED_LIFETIME = 'tax/avatax/queue_failed_lifetime';
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

    /**
     * If user is guest, ID to use for "name_id" option
     */
    const CUSTOMER_GUEST_ID = 'Guest';

    /**
     * Value to send as "customer_code" if "email" is selected and quote doesn't have email
     */
    const CUSTOMER_MISSING_EMAIL = 'No email';

    /**
     * Value to send as "customer_code" if "name_id" is selected and quote doesn't have name
     */
    const CUSTOMER_MISSING_NAME = 'No name';

    /**#@+
     * Error Action Options
     */
    const ERROR_ACTION_DISABLE_CHECKOUT = 1;

    const ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX = 2;
    /**#@-*/

    /**#@+
     * Tax Modes
     */
    const TAX_MODE_NO_ESTIMATE_OR_SUBMIT = 1;

    const TAX_MODE_ESTIMATE_ONLY = 2;

    const TAX_MODE_ESTIMATE_AND_SUBMIT = 3;
    /**#@-*/

    /**#@+
     * AvaTax API values
     */
    const API_URL_DEV = 'https://development.avalara.net';

    const API_URL_PROD = 'https://avatax.avalara.net';

    const API_PROFILE_NAME_DEV = 'Development';

    const API_PROFILE_NAME_PROD = 'Production';
    /**#@-*/

    const AVATAX_DOCUMENTATION_TAX_CODE_LINK
        = 'https://help.avalara.com/000_AvaTax_Calc/000AvaTaxCalc_User_Guide/051_Select_AvaTax_System_Tax_Codes/Tax_Codes_-_Frequently_Asked_Questions';

    /**
     * Magento version prefix
     */
    const API_APP_NAME_PREFIX = 'Magento 2';

    /**
     * Cache tag code
     */
    const AVATAX_CACHE_TAG = 'AVATAX';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var ProductMetadataInterface
     */
    protected $magentoProductMetadata = null;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var TaxClassRepositoryInterface
     */
    protected $taxClassRepository = null;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Class constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $magentoProductMetadata
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param State $appState
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $magentoProductMetadata,
        ATConfigFactory $avaTaxConfigFactory,
        State $appState,
        TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->magentoProductMetadata = $magentoProductMetadata;
        $this->avaTaxConfigFactory = $avaTaxConfigFactory;
        $this->appState = $appState;
        $this->taxClassRepository = $taxClassRepository;
        $this->createAvaTaxProfile();
        $this->urlBuilder = $urlBuilder;
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
     * Return tax mode
     *
     * @param $store
     * @return mixed
     */
    public function getTaxMode($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_TAX_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return whether to commit submitted transactions
     *
     * @param $store
     * @return mixed
     */
    public function getCommitSubmittedTransactions($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_COMMIT_SUBMITTED_TRANSACTIONS,
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
            'Line1' => $this->scopeConfig->getValue(
                self::XML_PATH_SHIPPING_ORIGIN_STREET_LINE1,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'Line2' => $this->scopeConfig->getValue(
                self::XML_PATH_SHIPPING_ORIGIN_STREET_LINE2,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'City' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_CITY,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'RegionId' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_REGION_ID,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'PostalCode' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'Country' => $this->scopeConfig->getValue(
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
     * Get SKU for Shipping
     *
     * @param null $store
     * @return string
     */
    public function getSkuShipping($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get SKU for Gift Wrap at the Order Level
     *
     * @param null $store
     * @return string
     */
    public function getSkuGiftWrapOrder($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_GIFT_WRAP_ORDER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get SKU for Gift Wrap at the Item Level
     *
     * @param null $store
     * @return string
     */
    public function getSkuShippingGiftWrapItem($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_GIFT_WRAP_ITEM,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get SKU for Gift Wrap card
     *
     * @param null $store
     * @return string
     */
    public function getSkuShippingGiftWrapCard($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_GIFT_WRAP_CARD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get SKU for positive adjustment
     *
     * @param null $store
     * @return string
     */
    public function getSkuAdjustmentPositive($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_ADJUSTMENT_POSITIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get SKU for negative adjustment
     *
     * @param null $store
     * @return string
     */
    public function getSkuAdjustmentNegative($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_ADJUSTMENT_NEGATIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Location Code
     *
     * @param null $store
     * @return string
     */
    public function getLocationCode($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_SKU_LOCATION_CODE,
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
     * Get action to take when error occurs
     *
     * @param null $store
     * @return string
     */
    public function getErrorAction($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ERROR_ACTION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return "disable checkout" error message based on the current area context
     *
     * @param null $store
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getErrorActionDisableCheckoutMessage($store = null)
    {
        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return __(
                $this->getErrorActionDisableCheckoutMessageBackend($store),
                $this->urlBuilder->getUrl('avatax/log')
            );
        } else {
            return __($this->getErrorActionDisableCheckoutMessageFrontend($store));
        }
    }

    /**
     * Get "disable checkout" error message for frontend user
     *
     * @param null $store
     * @return string
     */
    protected function getErrorActionDisableCheckoutMessageFrontend($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ERROR_ACTION_DISABLE_CHECKOUT_MESSAGE_FRONTEND,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get "disable checkout" error message for backend user
     *
     * @param null $store
     * @return string
     */
    protected function getErrorActionDisableCheckoutMessageBackend($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ERROR_ACTION_DISABLE_CHECKOUT_MESSAGE_BACKEND,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return if address validation is enabled
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return mixed
     */
    public function isAddressValidationEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns if user is allowed to choose between the original address and the validated address
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return mixed
     */
    public function allowUserToChooseAddress($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_METHOD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Instructions for the user if they have a choice between the original address and validated address
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getAddressValidationInstructionsWithChoice($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITH_CHOICE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Instructions for the user if they do not have a choice between the original address and the validated address
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getAddressValidationInstructionsWithoutChoice($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITHOUT_CHOICE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Instructions for the user if there was an error in validating their address
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return string
     */
    public function getAddressValidationErrorInstructions($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_ERROR_INSTRUCTIONS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns which countries were enabled to validate the users address
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param null $store
     * @return mixed
     */
    public function getAddressValidationCountriesEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ADDRESS_VALIDATION_COUNTRIES_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log level
     *
     * @param null $store
     * @return int
     */
    public function getLogDbLevel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_DB_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log detail
     *
     * @param null $store
     * @return int
     */
    public function getLogDbDetail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_DB_DETAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log lifetime
     *
     * @param null $store
     * @return int
     */
    public function getLogDbLifetime($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_DB_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return if file logging is enabled
     *
     * @param null $store
     * @return bool
     */
    public function getLogFileEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log mode
     *
     * @param null $store
     * @return int
     */
    public function getLogFileMode($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log level
     *
     * @param null $store
     * @return int
     */
    public function getLogFileLevel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log detail
     *
     * @param null $store
     * @return int
     */
    public function getLogFileDetail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_DETAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured queue max retry attempts
     *
     * @param null $store
     * @return int
     */
    public function getQueueMaxRetryAttempts($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_QUEUE_MAX_RETRY_ATTEMPTS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured queue complete lifetime
     *
     * @param null $store
     * @return int
     */
    public function getQueueCompleteLifetime($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_QUEUE_COMPLETE_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured queue failed lifetime
     *
     * @param null $store
     * @return int
     */
    public function getQueueFailedLifetime($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_QUEUE_FAILED_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
