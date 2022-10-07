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

namespace ClassyLlama\AvaTax\Test\Unit\Model\Queue\Consumer;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ApiLogConsumer
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\Queue\Consumer
 */
class ApiLogConsumerTest extends TestCase
{
	protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);
        
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->genericConfigMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueConfigMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueCollFactoryMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->restClientMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiLogConsumer = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer::class,
            [
                'logger' => $this->loggerMock,
                'genericConfig' => $this->genericConfigMock,
                'queueConfig' => $this->queueConfigMock,
                'queueCollFactory' => $this->queueCollFactoryMock,
                'restClient' => $this->restClientMock
            ]
        );

        $this->queueMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueMock->expects($this->once())
            ->method('getPayload')
            ->willReturn('[]');
        
		parent::setUp();
    }

    /**
     * tests consume
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer::consume
     */
    public function testConsume()
    {
        $this->assertIsArray($this->apiLogConsumer->consume($this->queueMock));
    }

}