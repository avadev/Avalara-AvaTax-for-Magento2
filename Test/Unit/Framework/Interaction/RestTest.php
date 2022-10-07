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
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

/**
 * Class RestTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest
 * @package ClassyLlama\AvaTax\Framework\Interaction
 */
class RestTest extends TestCase
{
    const API_MODE_PROD = 'production';

    const API_MODE_DEV = 'sandbox';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ClientPool
     */
    protected $clientPool;

    /** @var array */
    protected $clients = [];

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()
                                ->getMock();
        $this->dataObjectFactory = $this->getMockBuilder(DataObjectFactory::class)->disableOriginalConstructor()
                                ->getMock();
        $this->clientPool = $this->getMockBuilder(ClientPool::class)->disableOriginalConstructor()
                                ->getMock();
        
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest::class,
                [
                    "logger" => $this->logger,
                    "dataObjectFactory" => $this->dataObjectFactory,
                    "clientPool" => $this->clientPool
                ]
            );
    }

    /**
     * tests getClient
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest::getClient
     */
    public function testGetClient()
    {
        $this->testObject->getClient();
    }

    /**
     * tests ping
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest::ping
     */
    public function testPing()
    {
        $pingResultModel = $this->getMockBuilder(\Avalara\PingResultModel::class)->disableOriginalConstructor()
                                ->getMock();
        $this->avaTaxClient = $this->getMockBuilder(\Avalara\AvaTaxClient::class)->disableOriginalConstructor()
                                    ->getMock();
        $this->avaTaxClient
            ->expects($this->any())
            ->method('ping')
            ->willReturn($pingResultModel);
        $this->avaTaxClientWrapper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::class)->disableOriginalConstructor()
                                ->getMock();
        $this->avaTaxClientWrapper
            ->expects($this->any())
            ->method('withCatchExceptions')
            ->willReturn($this->avaTaxClient);
        $this->clientPool
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($this->avaTaxClientWrapper);
        $this->testObject->ping();
    }

    /**
     * tests ping
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest::ping
     */
    public function testPingException()
    {
        $message = "Test Exception Message";
        $request = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)->disableOriginalConstructor()
                                ->getMock();
        
                               
        $response = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)->disableOriginalConstructor()
                                ->getMock();
  
        $response
            ->expects($this->any())
            ->method('getBody')
            ->willReturn('{"error" : ["details" : "test response"]');
        $previous = $this->getMockBuilder(\Exception::class)->disableOriginalConstructor()
                                ->getMock();
        $requestException = $this->objectManager->getObject(
            \GuzzleHttp\Exception\RequestException::class, 
            [
                "message" => $message,
                "request" => $request,
                "response" => $response,
                "previous" => $previous
            ]
        );
        $this->avaTaxClient = $this->getMockBuilder(\Avalara\AvaTaxClient::class)->disableOriginalConstructor()
                                    ->getMock();
        $this->avaTaxClient
            ->expects($this->any())
            ->method('ping')
            ->willThrowException($requestException);
        $this->avaTaxClientWrapper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper::class)->disableOriginalConstructor()
                                ->getMock();
        $this->avaTaxClientWrapper
            ->expects($this->any())
            ->method('withCatchExceptions')
            ->willReturn($this->avaTaxClient);
        $this->clientPool
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($this->avaTaxClientWrapper);
        $this->expectException(\ClassyLlama\AvaTax\Exception\AvataxConnectionException::class);
        $this->testObject->ping();
    }
}
