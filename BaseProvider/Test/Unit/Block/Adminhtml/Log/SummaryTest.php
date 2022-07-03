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

namespace ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\CollectionFactory;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection;

/**
 * Class SummaryTest
 * @covers ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\Summary
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log
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
     * @covers ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log\Summary
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
            \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\Summary::class,
            [
                "context" => $this->context,
	            "logCollectionFactory" => $this->collectionFactoryMock
            ]
        );

    	parent::setUp();
    }

    /**
     * tests getLogCollection
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\Summary::getLogCollection
     */
    public function testGetLogCollection()
    {
        $this->collectionFactoryMock
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->collectionMock);

        $className = get_class($this->block);
        $reflection = new \ReflectionClass($className);
        $getButtonUrl = $reflection->getMethod('getLogCollection');
        $getButtonUrl->setAccessible(true);

       $this->assertEquals($this->collectionMock, $getButtonUrl->invoke($this->block));
    }

     /**
     * tests getSummaryData
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\Summary::getSummaryData
     */
    public function testGetSummaryData()
    {
        $this->collectionFactoryMock
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->collectionMock);

        $this->assertEquals(null, $this->block->getSummaryData());
    }
}
