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
namespace ClassyLlama\AvaTax\Test\Unit\Model\ResourceModel\Log;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
/**
 * Class CollectionTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model
 */
class CollectionTest extends TestCase
{	

    const SUMMARY_COUNT_FIELD_NAME = 'count';

	protected function setUp(): void
    {
    	/**
	     * Setup
	     * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::__construct
	     * {@inheritDoc}
	     */

    	$this->objectManager = new ObjectManager($this);

        $this->entityFactory = $this->getMockBuilder(\Magento\Framework\Data\Collection\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fetchStrategy = $this->getMockBuilder(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'columns', 'where'])
            ->getMock();
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resource = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getCurrentStoreIds', '_construct', 'getMainTable', 'getTable'])
            ->getMock();

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        
        $this->collectionModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::class,
            [
            	"entityFactory" => $this->entityFactory,
	            "logger" => $this->logger,
	            "fetchStrategy" => $this->fetchStrategy,
	            "eventManager" => $this->eventManager,
	            "connection" => $this->connection,
	            "resource" => $this->resource
            ]
        );
    	parent::setUp();
    }

    /**
    * tests _construct
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::_construct
    */
    public function test()
    {
        $this->assertEquals($this->collectionModel, $this->collectionModel);
    }


    /**
    * tests AddCreatedAtBeforeFilter
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::addCreatedAtBeforeFilter
    */
    public function testAddCreatedAtBeforeFilter()
    {   
        $this->assertEquals($this->collectionModel, $this->collectionModel->addCreatedAtBeforeFilter(12));
    }

    /**
    * tests GetLevelSummaryCount
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::getLevelSummaryCount
    */
    public function testGetLevelSummaryCount()
    {
        $this->assertEquals(null, $this->collectionModel->getLevelSummaryCount());
    }
}
