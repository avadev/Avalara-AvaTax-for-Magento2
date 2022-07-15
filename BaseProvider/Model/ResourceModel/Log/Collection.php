<?php
/*
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
namespace ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log;

use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime;

class Collection extends AbstractCollection
{

    const SUMMARY_COUNT_FIELD_NAME = 'count';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $genericLogger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $genericLogger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($entityFactory, $genericLogger, $fetchStrategy, $eventManager, $connection, $resource);
    }
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \ClassyLlama\AvaTax\BaseProvider\Model\Log::class,
            \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log::class
        );
    }

    /**
     * Filter collection by created at date older than specified seconds before now
     *
     * @param int $secondsBeforeNow
     * @return $this
     */
    public function addCreatedAtBeforeFilter($secondsBeforeNow)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new \DateInterval('PT' . $secondsBeforeNow . 'S');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter(Log::CREATED_AT_FIELD_NAME, ['lt' => $formattedDate]);
        return $this;
    }

    /**
     * Get the log count for all log levels
     *
     * @return array
     */
    public function getLevelSummaryCount()
    {
        $select = clone $this->getSelect();
        $connection = $this->getConnection();

        $countExpr = new \Zend_Db_Expr("COUNT(*)");

        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns([
            self::SUMMARY_COUNT_FIELD_NAME => $countExpr,
            Log::LEVEL_FIELD_NAME => Log::LEVEL_FIELD_NAME
        ]);
        $select->group(Log::LEVEL_FIELD_NAME);

        return $connection->fetchAll($select);
    }
}
