<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Helper\Config;

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
     * Task constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        CollectionFactory $queueCollectionFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueCollectionFactory = $queueCollectionFactory;
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
     */
    public function processPendingQueue()
    {
        $this->avaTaxLogger->debug(__('Starting queue processing'));

        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PENDING);

        // Process each queued entity
        /** @var $queue Queue */
        foreach ($queueCollection as $queue) {
            // Process queue
            try {
                $queue->process();

                // Increment process count statistic
                $this->processCount++;
            } catch (\Exception $e) {

                // Increment error count statistic
                $this->errorCount++;
                $this->errorMessages[] = $e->getMessage();
            }
        }

        $this->avaTaxLogger->debug(
            __('Finished queue processing'),
            [ /* context */
                'error_count' => $this->errorCount,
                'process_count' => $this->processCount
            ]
        );
    }

    /**
     * Reset hung queue records
     */
    public function resetHungQueuedRecords()
    {
        $this->avaTaxLogger->debug(__('Resetting hung queue records'));

        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PROCESSING);

        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $queueCollection->addCreatedAtBeforeFilter(86400);
        $queueCollection->addUpdatedAtBeforeFilter(86400);

        // TODO: Check for bug where records are reset that are less than a day old

        // Reset each hung queued entity
        /** @var $queue Queue */
        foreach ($queueCollection as $queue) {
            // Reset queue
            $queue->setQueueStatus(Queue::QUEUE_STATUS_PENDING);
            $queue->save();
            $this->resetCount++;
        }

        $this->avaTaxLogger->debug(
            __('Finished resetting hung queued records'),
            [ /* context */
                'reset_count' => $this->resetCount
            ]
        );
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

        $this->avaTaxLogger->debug(
            __('Finished queue clearing'),
            [ /* context */
                'delete_complete_count' => $this->deleteCompleteCount,
                'delete_failed_count' => $this->deleteFailedCount,
            ]
        );
    }

    // TODO: Possibly refactor clearCompleteQueue() and clearFailedQueue() into a single method

    /**
     * Clear the queue of complete records based on config lifetime
     */
    protected function clearCompleteQueue()
    {
        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
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
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
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
