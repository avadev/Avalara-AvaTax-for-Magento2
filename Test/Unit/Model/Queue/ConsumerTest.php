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
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;
/**
 * Class ConsumerTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\Queue
 */
class ConsumerTest extends TestCase
{
    /**
     * @var array
     */
    protected $processors = [];

	protected function setUp(): void
    {
		/**
		 * Setup
		 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::__construct
		 * {@inheritDoc}
		 */

		$this->objectManager = new ObjectManager($this);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->queueCollFactory = $this->getMockBuilder(QueueCollFactory::class)
			->disableOriginalConstructor()
			->setMethods(['create', 'addFieldToFilter','getSize','delete'])
			->getMock();

		$this->queueConfig = $this->createMock(QueueConfig::class);
        $this->consumerObj = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueInterface = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStatus','setAttempt','save'])
            ->getMockForAbstractClass();
		
		$this->consumer = $this->objectManager->getObject(
			\ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::class,
			[
				"queueCollFactory" => $this->queueCollFactory,
				"queueConfig" => $this->queueConfig,
				"logger" => $this->logger
			]
		);
		parent::setUp();
    }
	 /**
    * tests process
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::process
    */
    public function testProcess()
    {
        $this->queueCollFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queueCollFactory);

        $this->queueCollFactory->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn([$this->queueCollFactory]);

        $this->queueCollFactory->expects($this->any())
            ->method('getSize')
            ->willReturn(10);

        $this->queueCollectionMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumer->consumeJobs();
        $this->consumer->process();
    }
    /**
    * tests acknowledgeJob
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::acknowledgeJob
    */
    public function testAcknowledgeJob()
    {
        $attempt = 1;
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_PROCESSING;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setAttempt')
            ->with($attempt)
            ->willReturnSelf();
        
        $this->assertNotNull($this->consumer->acknowledgeJob($this->queueInterface));
    }
    /**
    * tests acknowledgeJob
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::acknowledgeJob
    */
	public function testAcknowledgeJobException()
    {
        $attempt = 1;
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_PROCESSING;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setAttempt')
            ->with($attempt)
            ->willReturnSelf();

        $e = new \Exception();
        $this->queueInterface->expects($this->any())
            ->method('save')
            ->willThrowException($e);
        $this->assertFalse($this->consumer->acknowledgeJob($this->queueInterface));
    }
    /**
    * tests markJobCompleted
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobCompleted
    */
	public function testMarkJobCompleted()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_COMPLETED;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf();
        
        $this->assertNotNull($this->consumer->markJobCompleted($this->queueInterface, $response));
    }
    /**
    * tests markJobCompleted
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobCompleted
    */
	public function testMarkJobCompletedException()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_COMPLETED;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf();

        $e = new \Exception();
        $this->queueInterface->expects($this->any())
            ->method('save')
            ->willThrowException($e);
        $this->assertFalse($this->consumer->markJobCompleted($this->queueInterface, $response));
    }
    /**
    * tests markJobNewForNextAttempt
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobNewForNextAttempt
    */
	public function testMarkJobNewForNextAttempt()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf();        
        $this->assertNotNull($this->consumer->markJobNewForNextAttempt($this->queueInterface, $response));
    }
    /**
    * tests markJobNewForNextAttempt
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobNewForNextAttempt
    */
	public function testMarkJobNewForNextAttemptException()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf();

        $e = new \Exception();
        $this->queueInterface->expects($this->any())
            ->method('save')
            ->willThrowException($e);
        $this->assertFalse($this->consumer->markJobNewForNextAttempt($this->queueInterface, $response));        
    }
    /**
    * tests markJobFailed
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobFailed
    */
	public function testmarkJobFailed()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_FAILED;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf();        
        $this->assertNotNull($this->consumer->markJobFailed($this->queueInterface, $response));
    }
    /**
    * tests markJobFailed
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer::markJobFailed
    */
	public function testMarkJobFailedException()
    {
        $response = 'success';
        $status = \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_FAILED;
        
        $this->queueInterface->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();
        $this->queueInterface->expects($this->any())
            ->method('setResponse')
            ->with($response)
            ->willReturnSelf(); 

        $e = new \Exception();
        $this->queueInterface->expects($this->any())
            ->method('save')
            ->willThrowException($e);
        
        $this->assertFalse($this->consumer->markJobFailed($this->queueInterface, $response));
    }
}