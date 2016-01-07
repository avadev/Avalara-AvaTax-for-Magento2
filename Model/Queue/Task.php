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
     * Task constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        CollectionFactory $queueCollectionFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * Entry point for cron job execution
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
     * Process pending queue records
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
}
