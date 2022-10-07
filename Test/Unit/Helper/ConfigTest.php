<?php
/*
 *
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ConfigTest
 * @covers \ClassyLlama\AvaTax\Helper\Config
 * @package ClassyLlama\AvaTax\Test\Unit\Helper
 */
class ConfigTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    
    const XML_PATH_AVATAX_MODULE_ENABLED = 'tax/avatax/enabled';

    const XML_PATH_AVATAX_TAX_MODE = 'tax/avatax/tax_mode';

    const XML_PATH_AVATAX_COMMIT_SUBMITTED_TRANSACTIONS = 'tax/avatax/commit_submitted_transactions';

    const XML_PATH_AVATAX_TAX_CALCULATION_COUNTRIES_ENABLED = 'tax/avatax/tax_calculation_countries_enabled';

    const XML_PATH_AVATAX_FILTER_TAX_BY_REGION = 'tax/avatax/filter_tax_by_region';

    const XML_PATH_AVATAX_REGION_FILTER_LIST = 'tax/avatax/region_filter_list';

    const XML_PATH_AVATAX_CALCULATE_BEFORE_DISCOUNT = 'tax/avatax/calculate_tax_before_discounts';

    const XML_PATH_AVATAX_LIVE_MODE = 'tax/avatax/live_mode';

    const XML_PATH_AVATAX_PRODUCTION_ACCOUNT_NUMBER = 'tax/avatax/production_account_number';

    const XML_PATH_AVATAX_PRODUCTION_LICENSE_KEY = 'tax/avatax/production_license_key';

    const XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE = 'tax/avatax/production_company_code';

    const XML_PATH_AVATAX_PRODUCTION_COMPANY_ID = 'tax/avatax/production_company_id';

    const XML_PATH_AVATAX_DEVELOPMENT_ACCOUNT_NUMBER = 'tax/avatax/development_account_number';

    const XML_PATH_AVATAX_DEVELOPMENT_LICENSE_KEY = 'tax/avatax/development_license_key';

    const XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE = 'tax/avatax/development_company_code';

    const XML_PATH_AVATAX_DEVELOPMENT_COMPANY_ID = 'tax/avatax/development_company_id';

    const XML_PATH_AVATAX_CUSTOMER_CODE_FORMAT = 'tax/avatax/customer_code_format';

    const XML_PATH_AVATAX_SKU_SHIPPING = 'tax/avatax/sku_shipping';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_ORDER = 'tax/avatax/sku_gift_wrap_order';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_ITEM = 'tax/avatax/sku_gift_wrap_item';

    const XML_PATH_AVATAX_SKU_GIFT_WRAP_CARD = 'tax/avatax/sku_gift_wrap_card';

    const XML_PATH_AVATAX_SKU_ADJUSTMENT_POSITIVE = 'tax/avatax/sku_adjustment_positive';

    const XML_PATH_AVATAX_SKU_ADJUSTMENT_NEGATIVE = 'tax/avatax/sku_adjustment_negative';

    const XML_PATH_AVATAX_SKU_LOCATION_CODE = 'tax/avatax/location_code';

    const XML_PATH_AVATAX_UPC_ATTRIBUTE = 'tax/avatax/upc_attribute';

    const XML_PATH_AVATAX_REF1_ATTRIBUTE = 'tax/avatax/ref1_attribute';

    const XML_PATH_AVATAX_REF2_ATTRIBUTE = 'tax/avatax/ref2_attribute';

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

    const XML_PATH_AVATAX_LOG_BUILTIN_ROTATE_ENABLED = 'tax/avatax/logging_file_builtin_rotation_enabled';

    const XML_PATH_AVATAX_LOG_BUILTIN_ROTATE_MAX_FILES = 'tax/avatax/logging_file_builtin_rotation_max_files';

    const XML_PATH_AVATAX_LOG_FILE_LEVEL = 'tax/avatax/logging_file_level';

    const XML_PATH_AVATAX_LOG_FILE_DETAIL = 'tax/avatax/logging_file_detail';

    const XML_PATH_AVATAX_QUEUE_MAX_RETRY_ATTEMPTS = 'tax/avatax/queue_max_retry_attempts';

    const XML_PATH_AVATAX_QUEUE_COMPLETE_LIFETIME = 'tax/avatax/queue_complete_lifetime';

    const XML_PATH_AVATAX_QUEUE_FAILED_LIFETIME = 'tax/avatax/queue_failed_lifetime';

    const XML_PATH_AVATAX_QUEUE_PROCESSING_TYPE = 'tax/avatax/queue_processing_type';

    const XML_PATH_AVATAX_QUEUE_ADMIN_NOTIFICATION_ENABLED = 'tax/avatax/queue_admin_notification_enabled';

    const XML_PATH_AVATAX_QUEUE_FAILURE_NOTIFICATION_ENABLED = 'tax/avatax/queue_failure_notification_enabled';

    const XML_PATH_AVATAX_ADMIN_NOTIFICATION_IGNORE_NATIVE_TAX_RULES = 'tax/avatax/ignore_native_tax_rules_notification';

    const XML_PATH_AVATAX_ADVANCED_RESPONSE_LOGGING = 'tax/avatax_advanced/response_logging_enabled';

    const XML_PATH_AVATAX_ADVANCED_API_TIMEOUT = 'tax/avatax_advanced/avatax_timeout';

    const XML_PATH_AVATAX_SHIPPING_TAX_CODE = 'tax/avatax/shipping_tax_code';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_CUSTOM_STATUS_NAME = 'tax/avatax_document_management/custom_status_name_certificate';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_APPROVED = 'tax/avatax_document_management/approved_status_name';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_DENIED = 'tax/avatax_document_management/denied_status_name';

    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_PENDING = 'tax/avatax_document_management/pending_status_name';

    const XML_PATH_AVATAX_ADVANCED_AVATAX_TABLE_EXEMPTIONS = 'tax/avatax_advanced/avatax_table_exemptions';

    const XML_PATH_AVATAX_VAT_TRANSPORT = 'tax/avatax_general/vat_transport';
    const XML_PATH_AVATAX_TAX_INCLUDED = 'tax/avatax/tax_included';
    /**#@-*/

    /**
     * List of countries that are enabled by default
     */
    static public $taxCalculationCountriesDefault = ['US', 'CA'];

    const DOCUMENT_MANAGEMENT_COUNTRIES_DEFAULT = ['US', 'CA'];

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

    const AVATAX_DOCUMENTATION_TAX_CODE_LINK = 'https://help.avalara.com/000_AvaTax_Calc/000AvaTaxCalc_User_Guide/051_Select_AvaTax_System_Tax_Codes/Tax_Codes_-_Frequently_Asked_Questions';

    /**
     * Magento version prefix
     */
    const API_APP_NAME_PREFIX = 'Magento 2';

    /**
     * Cache tag code
     */
    const AVATAX_CACHE_TAG = 'AVATAX';

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Helper\Config::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->productMetadataMock = $this->getMockBuilder(\Magento\Framework\App\ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxClassRepositoryMock = $this->getMockBuilder(\Magento\Tax\Api\TaxClassRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendUrlMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactoryInstance = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
            $this->dataObjectFactoryMock->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->metaDataObjectMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaDataObjectFactoryMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaDataObjectFactoryMock->method('create')->willReturn($this->metaDataObjectMock);
        $this->configHelper = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\Config::class,
            [
                'context' => $this->context,
                'magentoProductMetadata' => $this->productMetadataMock,
                'appState' => $this->appStateMock,
                'taxClassRepository' => $this->taxClassRepositoryMock,
                'backendUrl' => $this->backendUrlMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock,
                'metaDataObjectFactory' => $this->metaDataObjectFactoryMock
            ]
        );
        parent::setUp();
    }

    /**
     * tests get isModuleEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isModuleEnabled
     */
    public function testIsModuleEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_MODULE_ENABLED,
            )
            ->willReturn(true);
        $this->assertEquals(true, $this->configHelper->isModuleEnabled($storecode, self::SCOPE_STORE));
    }

    /**
     * tests is Response Logging Enabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isResponseLoggingEnabled
     */
    public function testIsResponseLoggingEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADVANCED_RESPONSE_LOGGING,
            )
            ->willReturn(true);
        $this->assertEquals(true, $this->configHelper->isResponseLoggingEnabled($storecode, self::SCOPE_STORE));
    }

    /**
     * tests getAvaTaxApiTimeout
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAvaTaxApiTimeout
     */
    public function testGetAvaTaxApiTimeout()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADVANCED_API_TIMEOUT,
            )
            ->willReturn(1.1);
        $this->assertEquals(1.1, $this->configHelper->getAvaTaxApiTimeout($storecode, self::SCOPE_STORE));
    }

    /**
     * tests getTaxMode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getTaxMode
     */
    public function testGetTaxMode()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_TAX_MODE,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getTaxMode($storecode));
    }

    /**
     * tests getCommitSubmittedTransactions
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getCommitSubmittedTransactions
     */
    public function testGetCommitSubmittedTransactions()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_COMMIT_SUBMITTED_TRANSACTIONS,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getCommitSubmittedTransactions($storecode));
    }

    /**
     * tests getTaxCalculationCountriesEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getTaxCalculationCountriesEnabled
     */
    public function testGetTaxCalculationCountriesEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_TAX_CALCULATION_COUNTRIES_ENABLED,
            )
            ->willReturn("US,CA");
        $this->assertEquals("US,CA", $this->configHelper->getTaxCalculationCountriesEnabled($storecode, self::SCOPE_STORE));
    }

    /**
     * tests isAddressTaxable
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isAddressTaxable
     */
    public function testIsAddressTaxableTrue()
    {
        $address = $this->getMockBuilder(\Magento\Framework\DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $storecode = self::STORE_CODE;
        $this->assertEquals(true, $this->configHelper->isAddressTaxable($address, $storecode));
    }

    /**
     * tests isAddressTaxable
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isAddressTaxable
     */
    public function testIsAddressTaxableFalse()
    {
        $address = $this->getMockBuilder(\Magento\Framework\DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $storecode = self::STORE_CODE;
        $args = array(self::XML_PATH_AVATAX_FILTER_TAX_BY_REGION, self::XML_PATH_AVATAX_REGION_FILTER_LIST);
        $returnValues = array(self::XML_PATH_AVATAX_REGION_FILTER_LIST => "67,68", self::XML_PATH_AVATAX_FILTER_TAX_BY_REGION => 1);
        $a = 0;
        foreach ($args as $arg) {
            $this->scopeConfigMock->expects($this->at($a++))
                 ->method('getValue')
                 ->with($arg)
                 ->willReturn($returnValues[$arg]);
        }
        $this->configHelper->isAddressTaxable($address, $storecode);
    }

    /**
     * tests getOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getOriginAddress
     */
    public function testGetOriginAddress()
    {
        $storecode = self::STORE_CODE;
        $this->configHelper->getOriginAddress($storecode);
    }

    /**
     * tests getOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getOriginAddress
     */
    public function testGetOriginAddressWithStoreObject()
    {
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $store->expects($this->any())
                        ->method('getId')
                        ->willReturn(1);
        $this->configHelper->getOriginAddress($store);
    }

    /**
     * tests getCustomerCodeFormat
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getCustomerCodeFormat
     */
    public function testGetCustomerCodeFormat()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_CUSTOMER_CODE_FORMAT,
            )
            ->willReturn("id");
        $this->assertEquals("id", $this->configHelper->getCustomerCodeFormat($storecode, self::SCOPE_STORE));
    }

    /**
     * tests getApplicationName
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getApplicationName
     */
    public function testGetApplicationName()
    {
        $this->assertIsString($this->configHelper->getApplicationName());
    }

    /**
     * tests getConnectorString
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getConnectorString
     */
    public function testGetConnectorString()
    {
        $this->assertIsString($this->configHelper->getConnectorString());
    }

    /**
     * tests getApplicationDomain
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getApplicationDomain
     */
    public function testGetApplicationDomain()
    {
        $this->assertIsString($this->configHelper->getApplicationDomain());
    }

    /**
     * tests isProductionMode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isProductionMode
     */
    public function testIsProductionMode()
    {
        $storecode = self::STORE_CODE;
        $this->assertIsBool($this->configHelper->isProductionMode($storecode, self::SCOPE_STORE));
    }

    /**
     * tests getMode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getMode
     */
    public function testGetMode()
    {
        $isProductionMode = true;
        $this->assertEquals(self::API_PROFILE_NAME_PROD, $this->configHelper->getMode($isProductionMode));
        $isProductionMode = false;
        $this->assertEquals(self::API_PROFILE_NAME_DEV, $this->configHelper->getMode($isProductionMode));
    }

    /**
     * tests getAccountNumber
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAccountNumber
     */
    public function testGetAccountNumber()
    {
        $store = self::STORE_CODE;
        $scopeType = self::SCOPE_STORE;
        $isProduction = null;
        $this->configHelper->getAccountNumber($store, $scopeType, $isProduction);
    }

    /**
     * tests getLicenseKey
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLicenseKey
     */
    public function testGetLicenseKey()
    {
        $store = self::STORE_CODE;
        $scopeType = self::SCOPE_STORE;
        $isProduction = null;
        $this->configHelper->getLicenseKey($store, $scopeType, $isProduction);
    }

    /**
     * tests getCompanyCode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getCompanyCode
     */
    public function testGetCompanyCode()
    {
        $store = self::STORE_CODE;
        $scopeType = self::SCOPE_STORE;
        $isProduction = null;
        $this->configHelper->getCompanyCode($store, $scopeType, $isProduction);
    }

    /**
     * tests getCompanyId
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getCompanyId
     */
    public function testGetCompanyId()
    {
        $store = self::STORE_CODE;
        $scopeType = self::SCOPE_STORE;
        $isProduction = null;
        $this->configHelper->getCompanyId($store, $scopeType, $isProduction);

        $args = array(self::XML_PATH_AVATAX_LIVE_MODE, self::XML_PATH_AVATAX_PRODUCTION_COMPANY_ID);
        $returnValues = array(self::XML_PATH_AVATAX_LIVE_MODE => 1, self::XML_PATH_AVATAX_PRODUCTION_COMPANY_ID => 1);
        $a = 0;
        foreach ($args as $arg) {
            $this->scopeConfigMock->expects($this->at($a++))
                    ->method('getValue')
                    ->with($arg)
                    ->willReturn($returnValues[$arg]);
        }
        $this->configHelper->getCompanyId($store, $scopeType, $isProduction);
        
    }

    /**
     * tests getSkuShipping
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuShipping
     */
    public function testGetSkuShipping()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_SHIPPING,
            )
            ->willReturn("Shipping");
        $this->assertEquals("Shipping", $this->configHelper->getSkuShipping($storecode));
    }

    /**
     * tests getSkuGiftWrapOrder
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuGiftWrapOrder
     */
    public function testGetSkuGiftWrapOrder()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_GIFT_WRAP_ORDER,
            )
            ->willReturn("GW");
        $this->assertEquals("GW", $this->configHelper->getSkuGiftWrapOrder($storecode));
    }

    /**
     * tests getSkuShippingGiftWrapItem
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuShippingGiftWrapItem
     */
    public function testGetSkuShippingGiftWrapItem()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_GIFT_WRAP_ITEM,
            )
            ->willReturn("GWITEM");
        $this->assertEquals("GWITEM", $this->configHelper->getSkuShippingGiftWrapItem($storecode));
    }

    /**
     * tests getSkuShippingGiftWrapCard
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuShippingGiftWrapCard
     */
    public function testGetSkuShippingGiftWrapCard()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_GIFT_WRAP_CARD,
            )
            ->willReturn("GWITEMWRAP");
        $this->assertEquals("GWITEMWRAP", $this->configHelper->getSkuShippingGiftWrapCard($storecode));
    }

    /**
     * tests getSkuAdjustmentPositive
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuAdjustmentPositive
     */
    public function testGetSkuAdjustmentPositive()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_ADJUSTMENT_POSITIVE,
            )
            ->willReturn("PADJ");
        $this->assertEquals("PADJ", $this->configHelper->getSkuAdjustmentPositive($storecode));
    }

    /**
     * tests getSkuAdjustmentNegative
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getSkuAdjustmentNegative
     */
    public function testGetSkuAdjustmentNegative()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_ADJUSTMENT_NEGATIVE,
            )
            ->willReturn("NADJ");
        $this->assertEquals("NADJ", $this->configHelper->getSkuAdjustmentNegative($storecode));
    }

    /**
     * tests getLocationCode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLocationCode
     */
    public function testGetLocationCode()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SKU_LOCATION_CODE,
            )
            ->willReturn("402342342");
        $this->assertEquals("402342342", $this->configHelper->getLocationCode($storecode));
    }

    /**
     * tests getUpcAttribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getUpcAttribute
     */
    public function testGetUpcAttribute()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_UPC_ATTRIBUTE,
            )
            ->willReturn("sku");
        $this->assertEquals("sku", $this->configHelper->getUpcAttribute());
    }

    /**
     * tests getRef1Attribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getRef1Attribute
     */
    public function testGetRef1Attribute()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_REF1_ATTRIBUTE,
            )
            ->willReturn("ref1");
        $this->assertEquals("ref1", $this->configHelper->getRef1Attribute());
    }

    /**
     * tests getRef2Attribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getRef2Attribute
     */
    public function testGetRef2Attribute()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_REF2_ATTRIBUTE,
            )
            ->willReturn("ref2");
        $this->assertEquals("ref2", $this->configHelper->getRef2Attribute());
    }

    /**
     * tests getUseBusinessIdentificationNumber
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getUseBusinessIdentificationNumber
     */
    public function testGetUseBusinessIdentificationNumber()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_USE_VAT,
            )
            ->willReturn("yes");
        $this->assertEquals("yes", $this->configHelper->getUseBusinessIdentificationNumber($storecode));
    }

    /**
     * tests getErrorAction
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getErrorAction
     */
    public function testGetErrorAction()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ERROR_ACTION,
            )
            ->willReturn("disabled");
        $this->assertEquals("disabled", $this->configHelper->getErrorAction($storecode));
    }

    /**
     * tests getErrorActionDisableCheckoutMessage
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getErrorActionDisableCheckoutMessage
     */
    public function testGetErrorActionDisableCheckoutMessage()
    {
        $storecode = self::STORE_CODE;
        $this->configHelper->getErrorActionDisableCheckoutMessage($storecode);
    }

    /**
     * tests getErrorActionDisableCheckoutMessage
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getErrorActionDisableCheckoutMessage
     */
    public function testGetErrorActionDisableCheckoutMessageAdmin()
    {
        $this->appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $storecode = self::STORE_CODE;
        $this->configHelper->getErrorActionDisableCheckoutMessage($storecode);
    }

    /**
     * tests isAddressValidationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isAddressValidationEnabled
     */
    public function testIsAddressValidationEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_ENABLED,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->isAddressValidationEnabled($storecode));
    }

    /**
     * tests allowUserToChooseAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::allowUserToChooseAddress
     */
    public function testAllowUserToChooseAddress()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_METHOD,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->allowUserToChooseAddress($storecode));
    }

    /**
     * tests getAddressValidationInstructionsWithChoice
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAddressValidationInstructionsWithChoice
     */
    public function testGetAddressValidationInstructionsWithChoice()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITH_CHOICE,
            )
            ->willReturn("Valid with choice");
        $this->assertEquals("Valid with choice", $this->configHelper->getAddressValidationInstructionsWithChoice($storecode));
    }

    /**
     * tests getAddressValidationInstructionsWithoutChoice
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAddressValidationInstructionsWithoutChoice
     */
    public function testGetAddressValidationInstructionsWithoutChoice()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_INSTRUCTIONS_WITHOUT_CHOICE,
            )
            ->willReturn("Valid without choice");
        $this->assertEquals("Valid without choice", $this->configHelper->getAddressValidationInstructionsWithoutChoice($storecode));
    }

    /**
     * tests getAddressValidationErrorInstructions
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAddressValidationErrorInstructions
     */
    public function testGetAddressValidationErrorInstructions()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_ERROR_INSTRUCTIONS,
            )
            ->willReturn("Error Instruction");
        $this->assertEquals("Error Instruction", $this->configHelper->getAddressValidationErrorInstructions($storecode));
    }

    /**
     * tests getAddressValidationCountriesEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getAddressValidationCountriesEnabled
     */
    public function testGetAddressValidationCountriesEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_COUNTRIES_ENABLED,
            )
            ->willReturn("US,CA");
        $this->assertEquals("US,CA", $this->configHelper->getAddressValidationCountriesEnabled($storecode));
    }

    /**
     * tests getLogDbLevel
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogDbLevel
     */
    public function testGetLogDbLevel()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_DB_LEVEL,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogDbLevel());
    }

    /**
     * tests getLogDbDetail
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogDbDetail
     */
    public function testGetLogDbDetail()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_DB_DETAIL,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogDbDetail());
    }

    /**
     * tests getLogDbLifetime
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogDbLifetime
     */
    public function testGetLogDbLifetime()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_DB_LIFETIME,
            )
            ->willReturn(60);
        $this->assertEquals(60, $this->configHelper->getLogDbLifetime());
    }

    /**
     * tests getLogFileEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileEnabled
     */
    public function testGetLogFileEnabled()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_FILE_ENABLED,
            )
            ->willReturn(true);
        $this->assertEquals(true, $this->configHelper->getLogFileEnabled());
    }

    /**
     * tests getLogFileMode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileMode
     */
    public function testGetLogFileMode()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_FILE_MODE,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogFileMode());
    }

    /**
     * tests getLogFileBuiltinRotateEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileBuiltinRotateEnabled
     */
    public function testGetLogFileBuiltinRotateEnabled()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_BUILTIN_ROTATE_ENABLED,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogFileBuiltinRotateEnabled());
    }

    /**
     * tests getLogFileBuiltinRotateMaxFiles
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileBuiltinRotateMaxFiles
     */
    public function testGetLogFileBuiltinRotateMaxFiles()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_BUILTIN_ROTATE_MAX_FILES,
            )
            ->willReturn(10);
        $this->assertEquals(10, $this->configHelper->getLogFileBuiltinRotateMaxFiles());
    }

    /**
     * tests getLogFileLevel
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileLevel
     */
    public function testGetLogFileLevel()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_FILE_LEVEL,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogFileLevel());
    }

    /**
     * tests getLogFileDetail
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getLogFileDetail
     */
    public function testGetLogFileDetail()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_LOG_FILE_DETAIL,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getLogFileDetail());
    }

    /**
     * tests getQueueMaxRetryAttempts
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueMaxRetryAttempts
     */
    public function testGetQueueMaxRetryAttempts()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_MAX_RETRY_ATTEMPTS,
            )
            ->willReturn(15);
        $this->assertEquals(15, $this->configHelper->getQueueMaxRetryAttempts());
    }

    /**
     * tests getQueueCompleteLifetime
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueCompleteLifetime
     */
    public function testGetQueueCompleteLifetime()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_COMPLETE_LIFETIME,
            )
            ->willReturn(150);
        $this->assertEquals(150, $this->configHelper->getQueueCompleteLifetime());
    }

    /**
     * tests getQueueFailedLifetime
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueFailedLifetime
     */
    public function testGetQueueFailedLifetime()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_FAILED_LIFETIME,
            )
            ->willReturn(180);
        $this->assertEquals(180, $this->configHelper->getQueueFailedLifetime());
    }

    /**
     * tests getQueueAdminNotificationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueAdminNotificationEnabled
     */
    public function testGetQueueAdminNotificationEnabled()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_ADMIN_NOTIFICATION_ENABLED,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getQueueAdminNotificationEnabled());
    }

    /**
     * tests getQueueProcessingType
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueProcessingType
     */
    public function testGetQueueProcessingType()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_PROCESSING_TYPE,
            )
            ->willReturn("Normal");
        $this->assertEquals("Normal", $this->configHelper->getQueueProcessingType());
    }

    /**
     * tests getQueueFailureNotificationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getQueueFailureNotificationEnabled
     */
    public function testGetQueueFailureNotificationEnabled()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_QUEUE_FAILURE_NOTIFICATION_ENABLED,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getQueueFailureNotificationEnabled());
    }

    /**
     * tests isNativeTaxRulesIgnored
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::isNativeTaxRulesIgnored
     */
    public function testIsNativeTaxRulesIgnored()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADMIN_NOTIFICATION_IGNORE_NATIVE_TAX_RULES,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->isNativeTaxRulesIgnored());
    }

    /**
     * tests getCalculateTaxBeforeDiscount
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getCalculateTaxBeforeDiscount
     */
    public function testGetCalculateTaxBeforeDiscount()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_CALCULATE_BEFORE_DISCOUNT,
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->configHelper->getCalculateTaxBeforeDiscount($storecode));
    }

    /**
     * tests getShippingTaxCode
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getShippingTaxCode
     */
    public function testGetShippingTaxCode()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SHIPPING_TAX_CODE,
            )
            ->willReturn("PC040143");
        $this->assertEquals("PC040143", $this->configHelper->getShippingTaxCode($storecode));
    }

    /**
     * tests getTableExemptions
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getTableExemptions
     */
    public function testGetTableExemptions()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADVANCED_AVATAX_TABLE_EXEMPTIONS,
            )
            ->willReturn("table1,table2");
        $expectedOutput = ["table1", "table2"];
        $this->assertEquals($expectedOutput, $this->configHelper->getTableExemptions());
    }

    /**
     * tests getConfigDataArray
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getConfigDataArray
     */
    public function testGetConfigDataArray()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADVANCED_AVATAX_TABLE_EXEMPTIONS,
            )
            ->willReturn("table1,table2");
        $expectedOutput = ["table1", "table2"];
        $this->assertEquals($expectedOutput, $this->configHelper->getConfigDataArray(self::XML_PATH_AVATAX_ADVANCED_AVATAX_TABLE_EXEMPTIONS));
    }

    /**
     * tests getConfigData
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getConfigData
     */
    public function testGetConfigData()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_SHIPPING_TAX_CODE,
            )
            ->willReturn("PC040143");
        $this->assertEquals("PC040143", $this->configHelper->getConfigData(self::XML_PATH_AVATAX_SHIPPING_TAX_CODE, $storecode));
    }
    /**
     * tests getTaxationPolicy
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getTaxationPolicy
     */
    public function testGetTaxationPolicy()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_TAX_INCLUDED,
            )
            ->willReturn("1");
        $this->assertEquals("1", $this->configHelper->getTaxationPolicy($storecode));
    }
    /**
     * tests getVATTransport
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Config::getVATTransport
     */
    public function testGetVATTransport()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_VAT_TRANSPORT,
            )
            ->willReturn("Seller");
        $this->assertEquals("Seller", $this->configHelper->getVATTransport($storecode));
    }
}
