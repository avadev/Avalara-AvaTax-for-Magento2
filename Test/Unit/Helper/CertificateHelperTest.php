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

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class CertificateHelperTest
 * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class CertificateHelperTest extends TestCase
{
    // 24 hours in seconds
    const CERTIFICATE_URL_EXPIRATION = (60 * 60 * 24);

    /**
     * @var array
     */
    protected $certificates = [];

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var UrlSigner
     */
    protected $urlSigner;

    /**
     * @var AvataxConfig
     */
    protected $avataxConfig;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $customerRest;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRest = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->avataxConfig = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlSigner = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\UrlSigner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->certificateHelperMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\CertificateHelper::class)
            ->disableOriginalConstructor()
            ->getMock();       
        $this->certificateHelper = $this->objectManagerHelper->getObject(
            \ClassyLlama\AvaTax\Helper\CertificateHelper::class,
            [
                'DataObjectFactory' => $this->dataObjectFactoryMock,
                'customerRest' => $this->customerRest,
                'avataxConfig' => $this->avataxConfig,
                'urlBuilder' => $this->urlBuilder,
                'urlSigner' => $this->urlSigner
            ]
        );
        parent::setUp();
    }
    
    /**
     * getCertificateDeleteUrl
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificateDeleteUrl
     */
    public function testGetCertificateDeleteUrl()
    {
		$certificateId = 1;
		$customerId = 1;
		$this->assertNull($this->certificateHelper->getCertificateDeleteUrl($certificateId, $customerId));
    }
    
    /**
     * getCertificateUrl
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificateUrl
     */
    public function testGetCertificateUrl()
    {
		$certificateId = 1;
		$customerId = 1;
		$this->assertNull($this->certificateHelper->getCertificateUrl($certificateId, $customerId));
    }
    /**
     * getCertificates
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificates
     */
    public function testGetCertificates()
    {
		$customerId = 1;
		$this->assertNull($this->certificateHelper->getCertificates($customerId));
    }
    /**
     * getCertificates
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificates
     */
    public function testGetCertificates2()
    {
		$customerId = null;
		$this->assertIsArray($this->certificateHelper->getCertificates($customerId));
    }
    /**
     * getCertificates
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificates
     */
    public function testGetCertificates3()
    {
		$customerId = 1;
       
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\CertificateHelper::class);
        $reflection_property = $reflection->getProperty('certificates');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->certificateHelper, ['1'=>'test']);
        
		$this->assertIsString($this->certificateHelper->getCertificates($customerId));
    }
    /**
     * getCertificateStatusNames
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificateStatusNames
     */
    public function testGetCertificateStatusNames()
    {
		$this->assertIsArray($this->certificateHelper->getCertificateStatusNames());
    }
    /**
     * getCertificateStatusNames
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateHelper::getCertificateStatusNames
     */
    public function testGetCertificateStatusNames2()
    {
        $this->avataxConfig
            ->expects($this->exactly(4))
            ->method('getConfigData')
            ->withConsecutive(
                [\ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_CUSTOM_STATUS_NAME],
                [\ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_APPROVED],
                [\ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_DENIED],
                [\ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls('testvalue','approved','denied','pending');
        
		$this->assertIsArray($this->certificateHelper->getCertificateStatusNames());
    }
    
}
