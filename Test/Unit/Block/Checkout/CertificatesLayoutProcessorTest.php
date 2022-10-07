<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\Checkout\CertificatesLayoutProcessor
 */
class CertificatesLayoutProcessorTest extends TestCase
{
    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Mock documentManagementConfig
     *
     * @var \ClassyLlama\AvaTax\Helper\DocumentManagementConfig|PHPUnit_Framework_MockObject_MockObject
     */
    private $documentManagementConfig;

    /**
     * Mock urlBuilder
     *
     * @var \Magento\Framework\UrlInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * Mock customerRest
     *
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRest;

    /**
     * Mock dataObjectFactoryInstance
     *
     * @var \Magento\Framework\DataObject|PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryInstance;

    /**
     * Mock dataObjectFactory
     *
     * @var \Magento\Framework\DataObjectFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactory;

    /**
     * Mock customerSession
     *
     * @var \Magento\Customer\Model\Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * Mock certificateHelper
     *
     * @var \ClassyLlama\AvaTax\Helper\CertificateHelper|PHPUnit_Framework_MockObject_MockObject
     */
    private $certificateHelper;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\Checkout\CertificatesLayoutProcessor
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->config->expects($this->any())
                                ->method('isModuleEnabled')
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('isAddressValidationEnabled')
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('getAddressValidationInstructionsWithChoice')
                                ->willReturn("Suggested with Choice");
        $this->config->expects($this->any())
                                ->method('getAddressValidationInstructionsWithoutChoice')
                                ->willReturn("Suggested without Choice");
        $this->config->expects($this->any())
                                ->method('getAddressValidationErrorInstructions')
                                ->willReturn("Invalid Address");
        $this->config->expects($this->any())
                                ->method('allowUserToChooseAddress')
                                ->willReturn(false);
        $this->config->expects($this->any())
                                ->method('getAddressValidationCountriesEnabled')
                                ->willReturn(true);

        $this->documentManagementConfig = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\DocumentManagementConfig::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->documentManagementConfig->expects($this->any())
                                ->method('isEnabled')
                                ->willReturn(true);
        $this->documentManagementConfig->expects($this->any())
                                ->method('getCheckoutLinkTextNewCertCertsExist')
                                ->willReturn("New Link -> Cert Exists");
        $this->documentManagementConfig->expects($this->any())
                                ->method('getCheckoutLinkTextNewCertNoCertsExist')
                                ->willReturn("New Link -> No Cert Exists");
        $this->documentManagementConfig->expects($this->any())
                                ->method('getCheckoutLinkTextManageExistingCert')
                                ->willReturn("Manage Cert");
        $this->documentManagementConfig->expects($this->any())
                                ->method('getEnabledCountries')
                                ->willReturn(["US"]);

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->urlBuilder->expects($this->any())
                                ->method('getUrl')
                                ->willReturn("avatax/certificates");
                                
        $this->customerRest = $this->createMock(\ClassyLlama\AvaTax\Api\RestCustomerInterface::class);
        $this->dataObjectFactoryInstance = $this->createMock(\Magento\Framework\DataObject::class);
        $this->dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);

        $this->customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->customer->expects($this->any())
                                ->method('getId')
                                ->willReturn(1);
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->customerSession->expects($this->any())
                                ->method('getCustomer')
                                ->willReturn($this->customer);
        $this->certificateHelper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\CertificateHelper::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->certificateHelper->expects($this->any())
                                ->method('getCertificates')
                                ->willReturn([]);

        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Block\Checkout\CertificatesLayoutProcessor::class,
            [
                'config' => $this->config,
                'documentManagementConfig' => $this->documentManagementConfig,
                'urlBuilder' => $this->urlBuilder,
                'customerRest' => $this->customerRest,
                'dataObjectFactory' => $this->dataObjectFactory,
                'customerSession' => $this->customerSession,
                'certificateHelper' => $this->certificateHelper,
            ]
        );
    }

    /**
     * tests process
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Checkout\CertificatesLayoutProcessor::process
     */
    public function testProcess()
    {
        $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["billing-step"]["children"]["payment"]["children"]["payments-list"]["config"] = [];
        $jsLayout["components"]["checkout"]["children"]["sidebar"]["children"]["summary"]["children"]["totals"]["children"]["tax"]["config"] = [];
        $this->assertIsArray($this->testObject->process($jsLayout));
    }
}
