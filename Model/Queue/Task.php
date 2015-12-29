<?php

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
    public $processCount = 0;

    /**
     * @var int
     */
    public $errorCount = 0;

    /**
     * @var array
     */
    public $errorMessages = [];

    /**
     * @var int
     */
    public $resetCount = 0;

    /**
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        CollectionFactory $queueCollectionFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    public function cronProcessQueue()
    {
        $this->avaTaxLogger->debug('Initiating queue processing from cron job');
        $this->processPendingQueue();
        $this->resetHungQueuedRecords();
    }

    /**
     * Process pending queue records
     */
    public function processPendingQueue()
    {
        $this->avaTaxLogger->debug('Starting queue processing');

        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PENDING);

        // Process each queued entity
        foreach ($queueCollection as $queue) {
            /** @var $queue Queue */

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
            'Finished queue processing',
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
        $this->avaTaxLogger->debug('Resetting hung queue records');

        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PROCESSING);

        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $queueCollection->addCreatedAtBeforeFilter(86400);
        $queueCollection->addUpdatedAtBeforeFilter(86400);

        // Reset each hung queued entity
        foreach ($queueCollection as $queue) {
            /** @var $queue Queue */

            // TODO: Reset queue
            $queue->setQueueStatus(Queue::QUEUE_STATUS_PENDING);
            $queue->save();
            $this->resetCount++;
        }

        $this->avaTaxLogger->debug(
            'Finished resetting hung queued records',
            [ /* context */
                'reset_count' => $this->resetCount
            ]
        );
    }
}
