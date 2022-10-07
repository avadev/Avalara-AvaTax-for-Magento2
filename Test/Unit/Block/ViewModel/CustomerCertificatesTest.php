<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\ViewModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates
 */
class CustomerCertificatesTest extends TestCase
{
    /**
     * Mock coreRegistry
     *
     * @var \Magento\Framework\Registry|PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistry;

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
     * Mock urlSigner
     *
     * @var \ClassyLlama\AvaTax\Helper\UrlSigner|PHPUnit_Framework_MockObject_MockObject
     */
    private $urlSigner;

    /**
     * Mock configResourceModel
     *
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $configResourceModel;

    /**
     * Mock urlBuilder
     *
     * @var \Magento\Framework\UrlInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * Mock certificateDeleteHelper
     *
     * @var \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper|PHPUnit_Framework_MockObject_MockObject
     */
    private $certificateDeleteHelper;

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
     * @var \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->customerRest = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestCustomerInterface::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->dataObjectFactoryInstance = $this->getMockBuilder(\Magento\Framework\DataObject::class)
                                                    ->disableOriginalConstructor()
                                                    ->getMock();
        $this->dataObjectFactory = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->urlSigner = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\UrlSigner::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->configResourceModel = $this->getMockBuilder(\ClassyLlama\AvaTax\Model\ResourceModel\Config::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->certificateDeleteHelper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\CertificateDeleteHelper::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->certificateHelper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\CertificateHelper::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::class,
            [
                'coreRegistry' => $this->coreRegistry,
                'customerRest' => $this->customerRest,
                'dataObjectFactory' => $this->dataObjectFactory,
                'urlSigner' => $this->urlSigner,
                'configResourceModel' => $this->configResourceModel,
                'urlBuilder' => $this->urlBuilder,
                'certificateDeleteHelper' => $this->certificateDeleteHelper,
                'certificateHelper' => $this->certificateHelper,
            ]
        );
    }

    /**
     * tests getCustomerId
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::getCustomerId
     */
    public function testGetCustomerId()
    {
        $this->testObject->getCustomerId();
    }

    /**
     * tests shouldShowWarning
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::shouldShowWarning
     */
    public function testShouldShowWarning()
    {
        $this->testObject->shouldShowWarning();
    }

    /**
     * tests getCertificateUrl
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::getCertificateUrl
     */
    public function testGetCertificateUrl()
    {
        $certificateId = 1;
        $this->testObject->getCertificateUrl($certificateId);
    }

    /**
     * tests getDeleteCertificateUrl
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::getDeleteCertificateUrl
     */
    public function testGetDeleteCertificateUrl()
    {
        $certificateId = 1;
        $this->testObject->getDeleteCertificateUrl($certificateId);
    }

    /**
     * tests getCertificates
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::getCertificates
     */
    public function testGetCertificates()
    {
        $this->testObject->getCertificates();
    }

    /**
     * tests getCertificateStatusName
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::getCertificateStatusName
     */
    public function testGetCertificateStatusName()
    {
        $this->testObject->getCertificateStatusName();
    }

    /**
     * tests isReadyForValidation
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\CustomerCertificates::isReadyForValidation
     */
    public function testIsReadyForValidation()
    {
        $status = "PENDING";
        $this->testObject->isReadyForValidation($status);
    }
}
