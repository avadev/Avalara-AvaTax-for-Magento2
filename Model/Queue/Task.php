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

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\Queue\Processing\ProcessingStrategyInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

/**
 * Queue Task
 */
class Task
{

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var int
     */
    protected $processCount = 0;

    /**
     * @var int
     */
    protected $errorCount = 0;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var int
     */
    protected $resetCount = 0;

    /**
     * @var int
     */
    protected $deleteCompleteCount = 0;

    /**
     * @var int
     */
    protected $deleteFailedCount = 0;


    /**
     * @var ProcessingStrategyInterface
     */
    private $queueProcessor;

    /**
     * Task constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param CollectionFactory $queueCollectionFactory
     * @param QueueProcessorProvider $queueProcessorProvider
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        CollectionFactory $queueCollectionFactory,
        QueueProcessorProvider $queueProcessorProvider
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->queueProcessor = $queueProcessorProvider->getQueueProcessor();
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return int
     */
    public function getProcessCount()
    {
        return $this->processCount;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getResetCount()
    {
        return $this->resetCount;
    }

    /**
     * @return int
     */
    public function getDeleteCompleteCount()
    {
        return $this->deleteCompleteCount;
    }

    /**
     * @return int
     */
    public function getDeleteFailedCount()
    {
        return $this->deleteFailedCount;
    }

    /**
     * Entry point for cron job execution of processing the queue
     */
    public function cronProcessQueue()
    {
        $this->avaTaxLogger->debug(__('Initiating queue processing from cron job'));
        $this->processPendingQueue();
        $this->resetHungQueuedRecords();
    }

    /**
     * Process pending queue records
     *
     * @param bool $limit
     */
    public function processPendingQueue($limit = false)
    {
        $this->avaTaxLogger->debug(__('Starting queue processing'));
        $this->queueProcessor->setLimit($limit);
        $this->queueProcessor->execute();
        $this->errorMessages = $this->queueProcessor->getErrorMessages();
        $this->processCount = $this->queueProcessor->getProcessCount();
        $this->errorCount = $this->queueProcessor->getErrorCount();

        $context = [
            'error_count'   => $this->errorCount,
            'process_count' => $this->processCount
        ];
        if ($this->getErrorCount() > 0) {
            $context['error_messages'] = implode("\n", $this->getErrorMessages());
        }

        $this->avaTaxLogger->debug(
            __('Finished queue processing'),
            $context
        );
    }

    /**
     * Reset hung queue records
     *
     */
    public function resetHungQueuedRecords()
    {
        $this->avaTaxLogger->debug(__('Resetting hung queue records'));

        // Initialize the queue collection
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PROCESSING);

        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $queueCollection->addCreatedAtBeforeFilter(86400);
        $queueCollection->addUpdatedAtBeforeFilter(86400);

        /* Reset all queue processing entries */
        $condition = implode(" ", $queueCollection->getSelect()->getPart(Select::SQL_WHERE));
        try {
            $this->resetCount = $queueCollection->updateTableRecords($condition,
                ['queue_status' => Queue::QUEUE_STATUS_PENDING]);

            $this->avaTaxLogger->debug(
                __('Finished resetting hung queued records'),
                [ /* context */
                  'reset_count' => $this->resetCount
                ]
            );
        } catch (Zend_Db_Select_Exception $exception) {
            $this->avaTaxLogger->error($exception->getMessage());
        }
    }

    /**
     * Entry point for cron job execution of clearing the queue
     */
    public function cronClearQueue()
    {
        $this->avaTaxLogger->debug(__('Initiating queue clearing from cron job'));
        $this->clearQueue();
    }

    /**
     * Clear the queue of complete and failed records
     */
    public function clearQueue()
    {
        $this->avaTaxLogger->debug(__('Starting queue clearing'));

        $this->clearCompleteQueue();
        $this->clearFailedQueue();
        $this->resetHungQueuedRecords();

        $this->avaTaxLogger->debug(
            __('Finished queue clearing'),
            [ /* context */
              'delete_complete_count' => $this->deleteCompleteCount,
              'delete_failed_count'   => $this->deleteFailedCount,
            ]
        );
    }

    /**
     * Clear the queue of complete records based on config lifetime
     */
    protected function clearCompleteQueue()
    {
        // Initialize the queue collection
        /** @var $queueCollection Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_COMPLETE);

        // Get configuration for record lifetime
        $completeDays = $this->avaTaxConfig->getQueueCompleteLifetime();

        // Calculate the number of seconds to adjust the filter
        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $secondsBeforeNow = $completeDays * 60 * 60 * 24;

        // Add filters
        $queueCollection->addCreatedAtBeforeFilter($secondsBeforeNow);
        $queueCollection->addUpdatedAtBeforeFilter($secondsBeforeNow);

        // Process each queued entity
        /** @var $queue Queue */
        foreach ($queueCollection as $queue) {
            // Remove the queue record
            $queue->delete();
            $this->deleteCompleteCount++;
        }

    }

    /**
     * Clear the queue of failed records based on config lifetime
     */
    protected function clearFailedQueue()
    {
        // Initialize the queue collection
        /** @var $queueCollection Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_FAILED);

        // Get configuration for record lifetime
        $completeDays = $this->avaTaxConfig->getQueueFailedLifetime();

        // Calculate the number of seconds to adjust the filter
        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $secondsBeforeNow = $completeDays * 60 * 60 * 24;

        // Add filters
        $queueCollection->addCreatedAtBeforeFilter($secondsBeforeNow);
        $queueCollection->addUpdatedAtBeforeFilter($secondsBeforeNow);

        // Process each queued entity
        /** @var $queue Queue */
        foreach ($queueCollection as $queue) {
            // Remove the queue record
            $queue->delete();
            $this->deleteFailedCount++;
        }

    }
}
