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

namespace ClassyLlama\AvaTax\Test\Unit\Model\ResourceModel\Queue;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;

/**
 * Class CollectionTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\ResourceModel\Queue
 */
class CollectionTest extends TestCase
{

    protected function setUp(): void
    {
        /**
         * Setup
         * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection::__construct
         * {@inheritDoc}
         */

        $this->objectManager = new ObjectManager($this);

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->setMethods(['where', 'from'])
            ->disableOriginalConstructor()
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
            \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection::class,
            [
                "connection" => $this->connection,
                "resource" => $this->resource
            ]
        );
        parent::setUp();
    }

    /**
     * tests _construct
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection::_construct
     */
    public function test()
    {
        $this->assertEquals($this->collectionModel, $this->collectionModel);
    }
}

