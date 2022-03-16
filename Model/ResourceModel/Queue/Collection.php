<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel\Queue;

use ClassyLlama\AvaTax\Model\ResourceModel\Queue;
use DateInterval;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime;
use ClassyLlama\AvaTax\Model\Queue as QueueModel;
use Zend_Db_Expr;
use Magento\Framework\DB\Select;

class Collection extends AbstractCollection
{
    /**#@+
     * Field Names
     */
    const SUMMARY_COUNT_FIELD_NAME = 'count';
    const SUMMARY_LAST_UPDATED_AT_FIELD_NAME = 'last_updated_at';
    const SUMMARY_LAST_CREATED_AT_FIELD_NAME = 'last_created_at';
    const SUMMARY_YEAR_WEEK = 'year_week';
    /**#@-*/

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param mixed $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\Queue', 'ClassyLlama\AvaTax\Model\ResourceModel\Queue');
    }

    /**
     * Filter collection by queue status
     *
     * @param string $queueStatus
     * @return $this
     */
    public function addQueueStatusFilter($queueStatus)
    {
        $this->addFieldToFilter(Queue::QUEUE_STATUS_FIELD_NAME, $queueStatus);

        return $this;
    }

    /**
     * Filter collection by created at date older than specified seconds before now
     *
     * @param int $secondsBeforeNow
     * @return $this
     * @throws \Exception
     */
    public function addCreatedAtBeforeFilter($secondsBeforeNow)
    {
        $datetime = new \DateTime('now', new DateTimeZone('UTC'));
        $storeInterval = new DateInterval('PT' . $secondsBeforeNow . 'S');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter(Queue::CREATED_AT_FIELD_NAME, ['lt' => $formattedDate]);

        return $this;
    }

    /**
     * Filter collection by updated at date older than specified seconds before now
     *
     * @param int $secondsBeforeNow
     * @return $this
     */
    public function addUpdatedAtBeforeFilter($secondsBeforeNow)
    {
        $datetime = new \DateTime('now', new DateTimeZone('UTC'));
        $storeInterval = new DateInterval('PT' . $secondsBeforeNow . 'S');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter(Queue::UPDATED_AT_FIELD_NAME, ['lt' => $formattedDate]);

        return $this;
    }

    /**
     * Get the queue count for a specific queue status
     *
     * @param string $queueStatus
     * @return int
     */
    public function getQueueSummaryCount($queueStatus)
    {
        $select = clone $this->getSelect();
        $connection = $this->getConnection();

        $countExpr = new Zend_Db_Expr("COUNT(*)");

        $select->reset(Select::COLUMNS);
        $select->columns([
            self::SUMMARY_COUNT_FIELD_NAME => $countExpr
        ]);
        $select->where(Queue::QUEUE_STATUS_FIELD_NAME . ' = ?', $queueStatus);

        return $connection->fetchOne($select);
    }

    /**
     * Get the last processing time from the queue
     *
     * @return string
     */
    public function getQueueSummaryLastProcessed()
    {
        $select = clone $this->getSelect();
        $connection = $this->getConnection();

        $updatedAtExpr = new Zend_Db_Expr('MAX(' . Queue::UPDATED_AT_FIELD_NAME . ')');

        $select->reset(Select::COLUMNS);
        $select->columns([
            self::SUMMARY_LAST_UPDATED_AT_FIELD_NAME => $updatedAtExpr
        ]);

        return $connection->fetchOne($select);
    }

    /**
     * Get stats from queue records that have been pending for more than a day
     *
     * @return array
     */
    public function getQueuePendingMoreThanADay()
    {
        $select = clone $this->getSelect();
        $connection = $this->getConnection();

        $countExpr = new Zend_Db_Expr("COUNT(*)");
        $createdAtExpr = new Zend_Db_Expr('MAX(' . Queue::CREATED_AT_FIELD_NAME . ')');
        $updatedAtExpr = new Zend_Db_Expr('MAX(' . Queue::UPDATED_AT_FIELD_NAME . ')');

        $select->reset(Select::COLUMNS);
        $select->columns([
            self::SUMMARY_COUNT_FIELD_NAME           => $countExpr,
            self::SUMMARY_LAST_CREATED_AT_FIELD_NAME => $createdAtExpr,
            self::SUMMARY_LAST_UPDATED_AT_FIELD_NAME => $updatedAtExpr
        ]);
        $select->where(Queue::QUEUE_STATUS_FIELD_NAME . ' = ?', QueueModel::QUEUE_STATUS_PENDING);
        $select->where("
            (
                " . Queue::CREATED_AT_FIELD_NAME . " < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) AND
                (
                    " . Queue::UPDATED_AT_FIELD_NAME . " IS NULL OR
                    " . Queue::UPDATED_AT_FIELD_NAME . " < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
                )
            )
        ");

        return $connection->fetchRow($select);
    }

    /**
     * Get stats from queue records that have failed
     *
     * @return array
     */
    public function getQueueFailureStats()
    {
        $select = clone $this->getSelect();
        $connection = $this->getConnection();

        $yearWeekExpr = new Zend_Db_Expr('YEARWEEK(' . Queue::CREATED_AT_FIELD_NAME . ')');
        $countExpr = new Zend_Db_Expr("COUNT(*)");

        $select->reset(Select::COLUMNS);
        $select->columns([
            self::SUMMARY_YEAR_WEEK        => $yearWeekExpr,
            self::SUMMARY_COUNT_FIELD_NAME => $countExpr
        ]);
        $select->where(Queue::QUEUE_STATUS_FIELD_NAME . ' = ?', QueueModel::QUEUE_STATUS_FAILED);
        $select->group('YEARWEEK(' . Queue::CREATED_AT_FIELD_NAME . ')');

        $result = $connection->fetchAll($select);

        $returnArray = [];
        foreach ($result as $record) {
            $returnArray[$record[self::SUMMARY_YEAR_WEEK]] = $record[self::SUMMARY_COUNT_FIELD_NAME];
        }

        return $returnArray;
    }

    /**
     * Update Data for given condition for collection
     *
     * @param $condition
     * @param $columnData
     * @return int
     */
    public function updateTableRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getMainTable(),
            $columnData,
            $where = $condition
        );
    }
}
