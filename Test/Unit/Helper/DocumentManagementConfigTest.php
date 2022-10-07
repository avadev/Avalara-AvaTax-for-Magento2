<?php
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class DocumentManagementConfigTest
 * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class DocumentManagementConfigTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED = 'tax/avatax_document_management/enabled';
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES = 'tax/avatax_document_management/enabled_countries';
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_no_certs_exist';
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST = 'tax/avatax_document_management/checkout_link_text_new_cert_certs_exist';
    const XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS = 'tax/avatax_document_management/checkout_link_text_manage_existing_certs';
    const XML_PATH_CERTCAPTURE_AUTO_VALIDATION = 'tax/avatax_certificate_capture/disable_certcapture_auto_validation';
 
    protected $mainConfig;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->documentManagementConfig = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::class
        );
    }
    /**
     * tests if module is isEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::isEnabled
     */
    public function testIsEnabled()
    {
        $storecode = self::STORE_CODE;
        $string = 1;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsBool(false, $this->documentManagementConfig->isEnabled($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getEnabledCountries
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::getEnabledCountries
     */
    public function testGetEnabledCountries()
    {
        $storecode = self::STORE_CODE;
        $string = 'us';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_ENABLED_COUNTRIES, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsArray($this->documentManagementConfig->getEnabledCountries($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getCheckoutLinkTextNewCertNoCertsExist
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::getCheckoutLinkTextNewCertNoCertsExist
     */
    public function testGetCheckoutLinkTextNewCertNoCertsExist()
    {
        $storecode = self::STORE_CODE;
        $string = 'us';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_NO_CERTS_EXIST, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsString($this->documentManagementConfig->getCheckoutLinkTextNewCertNoCertsExist($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getCheckoutLinkTextNewCertCertsExist
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::getCheckoutLinkTextNewCertCertsExist
     */
    public function testGetCheckoutLinkTextNewCertCertsExist()
    {
        $storecode = self::STORE_CODE;
        $string = 'us';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_NEW_CERT_CERTS_EXIST, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsString($this->documentManagementConfig->getCheckoutLinkTextNewCertCertsExist($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getCheckoutLinkTextManageExistingCert
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::getCheckoutLinkTextManageExistingCert
     */
    public function testGetCheckoutLinkTextManageExistingCert()
    {
        $storecode = self::STORE_CODE;
        $string = 'us';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CHECKOUT_LINK_TEXT_MANAGE_EXISTING_CERTS, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsString($this->documentManagementConfig->getCheckoutLinkTextManageExistingCert($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is isCertificatesAutoValidationDisabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\DocumentManagementConfig::isCertificatesAutoValidationDisabled
     */
    public function testIsCertificatesAutoValidationDisabled()
    {
        $storecode = self::STORE_CODE;
        $data = true;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_CERTCAPTURE_AUTO_VALIDATION, self::SCOPE_STORE)
            ->willReturn($data);
        $this->assertIsBool($this->documentManagementConfig->isCertificatesAutoValidationDisabled($storecode, self::SCOPE_STORE));
    }
}
