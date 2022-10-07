<?php
/*
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
namespace ClassyLlama\AvaTax\Test\Unit\Model\Queue;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\BaseProvider\Helper\Config as QueueConfig;
use ClassyLlama\AvaTax\BaseProvider\Model\QueueFactory as QueueFactory;
/**
 * Class ProducerTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\Queue
 */
class ProducerTest extends TestCase
{
    protected function setUp(): void
    {
		/**
		 * Setup
		 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer::__construct
		 * {@inheritDoc}
		 */

		$this->objectManager = new ObjectManager($this);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->queueFactory = $this->getMockBuilder(QueueFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create','setClient', 'setPayload', 'setStatus', 'setAttempt', 'save'])			
			->getMock();

		$this->queueConfig = $this->createMock(QueueConfig::class);
        
		$this->producer = $this->objectManager->getObject(
			\ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer::class,
			[
				"logger" => $this->logger,
                "queueConfig" => $this->queueConfig,
                "queueFactory" => $this->queueFactory
			]
		);
		parent::setUp();
    }
	 /**
    * tests addJob
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer::addJob
    */
    public function testAddJob()
    {
        $payload = json_encode(['payload']);
        $attempt = \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::MIN_ATTEMPT;
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW;
        $client  = \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer::CLIENT;
        $this->queueFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queueFactory);
        $this->queueFactory->expects($this->any())
            ->method('setClient')
            ->with($client)
            ->willReturnSelf();
        $this->queueFactory->expects($this->any())
            ->method('setPayload')
            ->with($payload)
            ->willReturnSelf();
        $this->queueFactory->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueFactory->expects($this->any())
            ->method('setAttempt')
            ->with($attempt)
            ->willReturnSelf();
        
        $this->assertTrue($this->producer->addJob($client, $payload));
    }
    /**
    * tests addJob
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer::addJob
    */
    public function testAddJobException()
    {
        $payload = json_encode(['payload']);
        $client  = \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer::CLIENT;
        $e = new \Exception();
        $this->queueFactory->expects($this->any())
            ->method('create')
            ->willThrowException($e);
        $this->expectException(\Exception::class);
        $this->producer->addJob($client, $payload);
    }
	
}