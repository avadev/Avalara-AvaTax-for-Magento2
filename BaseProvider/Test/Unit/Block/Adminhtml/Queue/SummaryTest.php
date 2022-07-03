<?php
/*
 *
 * Avalara_BaseProvider
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

namespace ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Queue;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection;

/**
 * Class SummaryTest
 * @covers ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\Summary
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Queue
 */

class SummaryTest extends TestCase
{
    /**
     * @var collectionFactory|MockObject
     */
    public $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * Setup
     * @covers ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Queue\Summary
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create','getLevelSummaryCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();    

        $this->block = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\Summary::class,
            [
                "context" => $this->context,
	            "queueCollectionFactory" => $this->collectionFactoryMock
            ]
        );

    	parent::setUp();
    }

    /**
     * tests getQueueCollection
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\Summary::getQueueCollection
     */
    public function testGetQueueCollection()
    {
        $this->collectionFactoryMock
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->collectionMock);

        $className = get_class($this->block);
        $reflection = new \ReflectionClass($className);
        $getButtonUrl = $reflection->getMethod('getQueueCollection');
        $getButtonUrl->setAccessible(true);

       $this->assertEquals($this->collectionMock, $getButtonUrl->invoke($this->block));
    }
}
