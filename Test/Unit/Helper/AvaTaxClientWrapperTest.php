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

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use ClassyLlama\AvaTax\Helper\Config as ConfigHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use \Avalara\AvaTaxClient;

use GuzzleHttp\Psr7\Stream;
/**
 * Class AvaTaxClientWrapperTest
 * @covers \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class AvaTaxClientWrapperTest extends TestCase
{
    protected $objectManagerHelper;
    protected $context;
    protected $controller;
    protected $resultFactoryMock;
    protected $resultMock;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [ 'getDefaultStoreView' ]
            )
            ->getMockForAbstractClass();
        
        
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObject
        ->expects($this->any())
        ->method('setData')
        ->willReturn($this->dataObject);

        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        
        
        $this->dataObjectFactoryMock->method('create')->willReturn($this->dataObject);

        $this->configHelperMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->avaTaxClientWrapperMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();               
        
        $this->AvaTaxClientMock = $this->getMockBuilder(\Avalara\AvaTaxClient::class)
        ->disableOriginalConstructor()
        ->getMock(); 
       
        //response code started
        $response = [
            "description" => "Request processed successfully",
            "error" => "" ,
            "status_code" => 200
        ];
        $response = json_encode($response);
        $this->clientResponseMock = $this->getMockBuilder(\GuzzleHttp\Psr7\Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientResponseMock->expects($this->once())->method('getBody')->willReturn(new Stream(fopen('data://text/plain,' . $response,'r')));
       
        //response code ended
        
        $this->avaTaxClientWrapper = $this->objectManagerHelper->getObject(
            \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::class,
            [
                'dataObjectFactory' => $this->dataObjectFactoryMock,
                'configHelper' => $this->configHelperMock,
                'loggerInterface' => $this->loggerMock,
                'appName' => 'Avalara API',
                'appVersion' => '1.0',
                'machineName' => 'local',
                'environment' => 'localhost',
                'guzzleParams' => []
            ]
        );
        
        parent::setUp();
    }
    
    /**
     * downloadCertificateImage
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::downloadCertificateImage
     */
    public function testDownloadCertificateImage()
    {
		$companyId = 1;
		$id = 1;
		$page = 1;
		$type = 'test';
		$method = 'GET';
        $path = "/api/v2/companies/{$companyId}/certificates/{$id}/attachment";
        $guzzleParams = [
							'query' => ['$page' => 1, 'type' => $type],
							'body' => null,
							'headers' => [
								'Accept' => '*/*'
							]
						];

        $this->avaTaxClientWrapper->withSecurity("test","pass");
        $this->clientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->getMock(); 
        $this->avaTaxClientWrapper->setAvaClient($this->clientMock);

        $this->clientResponseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200); 
        
        $this->clientMock->expects($this->any())
            ->method('request')
            ->willReturn($this->clientResponseMock);
        
        $this->avaTaxClientWrapper->downloadCertificateImage($companyId, $id, $page, $type);
    }

    /**
     * downloadCertificateImage in auth else
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::downloadCertificateImage
     */
    public function testAuthElseDownloadCertificateImage()
    {
        $companyId = 1;
		$id = 1;
		$page = 1;
		$type = 'test';
		$method = 'GET';
        $path = "/api/v2/companies/{$companyId}/certificates/{$id}/attachment";
        $guzzleParams = [
							'query' => ['$page' => 1, 'type' => $type],
							'body' => null,
							'headers' => [
								'Accept' => '*/*'
							]
						];
        
        $this->avaTaxClientWrapper->withBearerToken("test");

        $this->clientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->getMock(); 
        $this->avaTaxClientWrapper->setAvaClient($this->clientMock);

        $this->clientResponseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200); 
        
        $this->clientMock->expects($this->any())
            ->method('request')
            ->willReturn($this->clientResponseMock);

        $this->avaTaxClientWrapper->downloadCertificateImage($companyId, $id, $page, $type);
    }

}
