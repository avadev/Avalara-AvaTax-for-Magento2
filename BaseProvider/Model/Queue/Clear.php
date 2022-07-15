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
namespace ClassyLlama\AvaTax\BaseProvider\Model\Queue;

use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\BaseProvider\Helper\Config as QueueConfig;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Clear
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QueueCollFactory
     */
    protected $queueCollFactory;

    /**
     * @var QueueConfig
     */
    protected $queueConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param LoggerInterface 
     * @param QueueConfig $queueConfig
     * @param QueueCollFactory $queueCollFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        LoggerInterface $logger,
        QueueConfig $queueConfig,
        QueueCollFactory $queueCollFactory,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->queueConfig = $queueConfig;
        $this->queueCollFactory = $queueCollFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Initiates the clear queues and queue process
     *
     * @return void
     */
    public function process()
    {
        $this->logger->debug(__('Initiating queue clearing from cron job'));
        $size = $this->clearDbQueues();
        $this->logger->debug(
            __('Completed queue clearing from cron job. Total Deleted: ' . $size),
            [
                'delete_count' => $size,
                'extra' => [
                    'class' => __METHOD__
                ]
            ]
        );
    }

    public function clearDbQueues()
    {
        $limit = $this->queueConfig->getQueueLimit();
        if ($limit == '') {
            return 0;
        }
        $filteredDate = $this->getFilterDate($limit);
        $queues = $this->queueCollFactory->create()
            ->addFieldToFilter('creation_time', ['lteq' => $filteredDate]);
        $size = 0;
        foreach ($queues as $queue) {
            try {
                $queue->delete();
                $size++;
            } catch(\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        return $size;
    }

    private function getFilterDate($days)
    {
        if ($days == 0) {
            return $this->dateTime->gmtDate('Y-m-d');
        } else {
            return $this->dateTime->gmtDate('Y-m-d', strtotime('-' . $days . ' day'));
        }
        
    }
}
