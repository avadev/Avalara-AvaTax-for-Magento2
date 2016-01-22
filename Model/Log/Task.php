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

namespace ClassyLlama\AvaTax\Model\Log;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Log;
use ClassyLlama\AvaTax\Model\ResourceModel\Log\CollectionFactory;

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
     * @var int
     */
    protected $deleteCount = 0;

    /**
     * @var CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * Task constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param CollectionFactory $logCollectionFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        CollectionFactory $logCollectionFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->logCollectionFactory = $logCollectionFactory;
    }

    /**
     * @return int
     */
    public function getDeleteCount()
    {
        return $this->deleteCount;
    }

    /**
     * Entry point for cron job execution of clearing the queue
     */
    public function cronClearLogs()
    {
        $this->avaTaxLogger->debug(__('Initiating queue clearing from cron job'));
        $this->clearLogs();
    }

    /**
     * Clear the queue of complete and failed records
     */
    public function clearLogs()
    {
        $this->avaTaxLogger->debug(__('Starting queue clearing'));

        /** @var $collection \ClassyLlama\AvaTax\Model\ResourceModel\Log\Collection */
        $collection = $this->logCollectionFactory->create();

        // Get configuration for record lifetime
        $lifetimeDays = $this->avaTaxConfig->getLogDbLifetime();

        // Calculate the number of seconds to adjust the filter
        // 86400 seconds == 60 seconds * 60 minutes * 24 hours == 1 day
        $secondsBeforeNow = $lifetimeDays * 60 * 60 * 24;

        // Add filters
        $collection->addCreatedAtBeforeFilter($secondsBeforeNow);

        // Process each queued entity
        /** @var $log Log */
        foreach ($collection as $log) {
            // Remove the queue record
            $log->delete();
            $this->deleteCount++;
        }

        $this->avaTaxLogger->debug(
            __('Finished clearing log entries'),
            [ /* context */
                'delete_count' => $this->deleteCount,
            ]
        );
    }
}
