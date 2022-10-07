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
use Magento\Framework\Stdlib\DateTime\DateTime;
/**
 * Class ClearTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\Queue
 */
class ClearTest extends TestCase
{
	protected function setUp(): void
    {
		/**
		 * Setup
		 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::__construct
		 * {@inheritDoc}
		 */

		$this->objectManager = new ObjectManager($this);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->queueCollFactory = $this->getMockBuilder(QueueCollFactory::class)
			->disableOriginalConstructor()
			->setMethods(['create', 'addFieldToFilter','getSize','delete'])
			->getMock();

		$this->queueConfig = $this->createMock(QueueConfig::class);
		
		$this->dateTime = $this->createMock(DateTime::class);

		$this->clear = $this->objectManager->getObject(
			\ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::class,
			[
				"queueCollFactory" => $this->queueCollFactory,
				"queueConfig" => $this->queueConfig,
				"logger" => $this->logger,
				"dateTime" => $this->dateTime
			]
		);
		parent::setUp();
    }
	 /**
    * tests process
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::process
    */
    public function testProcess()
    {
        $this->queueConfig->expects($this->any())
            ->method('getQueueLimit')
            ->willReturn(10);

        $this->queueCollFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queueCollFactory);

        $this->queueCollFactory->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn([$this->queueCollFactory]);

        $this->queueCollFactory->expects($this->any())
            ->method('getSize')
            ->willReturn(10);

         $this->queueCollFactory->expects($this->any())
            ->method('delete')
            ->willReturnSelf();

        $this->clear->clearDbQueues();
        $this->clear->process();
    }
	 /**
    * tests process
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::process
    */
    public function testProcess2()
    {
        $this->queueConfig->expects($this->any())
            ->method('getQueueLimit')
            ->willReturn('');

        $this->clear->clearDbQueues();
        $this->clear->process();
    }
	/**
    * tests process
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::process
    */
    public function testProcess3()
    {
        $this->queueConfig->expects($this->any())
            ->method('getQueueLimit')
            ->willReturn('0');

        $this->queueCollFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queueCollFactory);

        $this->queueCollFactory->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn([$this->queueCollFactory]);

        $this->queueCollFactory->expects($this->any())
            ->method('getSize')
            ->willReturn(1);

         $this->queueCollFactory->expects($this->any())
            ->method('delete')
            ->willReturnSelf();

        $this->assertIsInt($this->clear->clearDbQueues());
    }

    /**
    * tests clearDbQueues
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Clear::clearDbQueues
    */
    public function testClearDbQueues()
    {
        $this->queueConfig->expects($this->any())
            ->method('getQueueLimit')
            ->willReturn(10);

        $this->queueCollFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queueCollFactory);

        $this->queueCollFactory->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn([$this->queueCollFactory]);
        $e = new \Exception();
        $this->queueCollFactory->expects($this->any())
            ->method('delete')
            ->willThrowException($e);
        $this->assertIsInt($this->clear->clearDbQueues());
    }
}