<?php

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;

/**
 * Queue Task
 */
class Task
{
    /*
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /*
     * @var int
     */
    public $processCount = 0;

    /**
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        CollectionFactory $queueCollectionFactory
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    public function cronProcessQueue()
    {
        $this->processPendingQueue();
    }

    /*
     *
     */
    public function processPendingQueue()
    {
        // Initialize the queue collection
        /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter('pending');

        // Process each queued entity
        foreach ($queueCollection as $queue) {
            /** @var $queue \ClassyLlama\AvaTax\Model\Queue */

            // Process queue
            $queue->process();

            // Increment process count statistic
            $this->processCount++;
        }


    }
}
