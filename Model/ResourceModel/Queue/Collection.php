<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    const SUMMARY_COUNT_FIELD_NAME = 'count';
    const SUMMARY_LAST_UPDATED_AT_FIELD_NAME = 'last_updated_at';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        $this->addFieldToFilter('queue_status', $queueStatus);
        return $this;
    }

    /**
     * Filter collection by created at date older than number of days
     *
     * @param int $seconds
     * @return $this
     */
    public function addCreatedAtBeforeFilter($seconds)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new \DateInterval('PT' . $seconds . 'S');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter('created_at', ['lt' => $formattedDate]);
        return $this;
    }

    /**
     * Filter collection by updated at date older than number of seconds
     *
     * @param int $seconds
     * @return $this
     */
    public function addUpdatedAtBeforeFilter($seconds)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new \DateInterval('PT' . $seconds . 'S');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter('created_at', ['lt' => $formattedDate]);
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

        $countExpr = new \Zend_Db_Expr("COUNT(*)");

        $select->reset(\Zend_DB_Select::COLUMNS);
        $select->columns([
                self::SUMMARY_COUNT_FIELD_NAME => $countExpr
            ]);
        $select->where(\ClassyLlama\AvaTax\Model\ResourceModel\Queue::QUEUE_STATUS_FIELD_NAME . ' = ?', $queueStatus);

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

        $updatedAtExpr = new \Zend_Db_Expr("MAX(updated_at)");

        $select->reset(\Zend_DB_Select::COLUMNS);
        $select->columns([
            self::SUMMARY_LAST_UPDATED_AT_FIELD_NAME => $updatedAtExpr
        ]);

        return $connection->fetchOne($select);
    }
}
