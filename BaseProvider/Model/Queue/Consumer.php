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
use ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\DefaultConsumer;

class Consumer
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
     * @var array
     */
    protected $processors = [];

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @param LoggerInterface 
     * @param QueueConfig $queueConfig
     * @param QueueCollFactory $queueCollFactory
     * @param array $processors
     */
    public function __construct(
        LoggerInterface $logger,
        QueueConfig $queueConfig,
        QueueCollFactory $queueCollFactory,
        array $processors = []
    ) {
        $this->logger = $logger;
        $this->queueConfig = $queueConfig;
        $this->queueCollFactory = $queueCollFactory;
        $this->processors = $processors;
        $this->batchSize = $this->queueConfig->getBatchSize();
    }

    /**
     * Initiates queue process
     *
     * @return void
     */
    public function process()
    {
        $this->logger->debug(__('Initiating queue processing from cron job'));
        $size = $this->consumeJobs();
        $this->logger->debug(
            __('Completed queue processing from cron job.'),
            [
                'total_count' => $size,
                'extra' => [
                    'class' => __METHOD__
                ]
            ]
        );
    }

    /**
     * @return array
     */
    public function consumeJobs()
    {
        $processors = $this->processors;
        $processedCount = [];
        foreach ($processors as $client=>$processor) {
            $queueJobs = $this->getNewJobs($processor);
            if ($queueJobs->getSize()) {
                $processedCount[$client] = $this->consumeClientJobs($processor, $queueJobs);
            } else {
                $processedCount[$client] = 0;
            }
        }
        return $processedCount;
    }

    /**
     * @param DefaultConsumer $processor
     * @param \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection $queueJobs
     * @return int
     */
    public function consumeClientJobs(DefaultConsumer $processor, \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection $queueJobs)
    {
        $counter = 0;
        foreach($queueJobs as $queueJob) {
            if ($counter >= $this->batchSize) {
                break;
            }
            $queueJob = $this->acknowledgeJob($queueJob);
            if ($queueJob) {
                $response = "";
                try {
                    list($sucess, $response) = $processor->consume($queueJob);
                    if ($sucess) {
                        $this->markJobCompleted($queueJob, $response);
                        $counter++;
                    } else {
                        if ($queueJob->getAttempt() >= \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::MAX_ATTEMPT) {
                            $this->markJobFailed($queueJob, $response);
                        } else {
                            $this->markJobNewForNextAttempt($queueJob, $response);
                        } 
                    }
                } catch (\Exception $e) {
                    if ($queueJob->getAttempt() >= \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::MAX_ATTEMPT) {
                        $this->markJobFailed($queueJob, $response);
                    } else {
                        $this->markJobNewForNextAttempt($queueJob, $response);
                    }
                    
                    $this->logger->critical($e->getMessage());
                }
            }
        }
        return $counter;
    }

    /**
     * @param DefaultConsumer $processor
     * @return \ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection
     */
    protected function getNewJobs(DefaultConsumer $processor)
    {
        return $processor->getJobs()
                         ->addFieldToFilter('status', ['eq' => \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW])
                         ->addFieldToFilter('attempt', ['lt' => \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::MAX_ATTEMPT]);
    }

    /**
     * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob
     * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface | boolean
     */
    public function acknowledgeJob(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob)
    {
        try {
            $queueJob->setStatus(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_PROCESSING)
                     ->setAttempt($queueJob->getAttempt() + 1)
                     ->save();
            return $queueJob;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
    }

    /**
     * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob
     * @param string $response
     * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface | boolean
     */
    public function markJobCompleted(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob, string $response)
    {
        try {
            $queueJob->setStatus(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_COMPLETED)
                     ->setResponse($response)
                     ->save();
            return $queueJob;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
    }

    /**
     * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob
     * @param string $response
     * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface | boolean
     */
    public function markJobNewForNextAttempt(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob, string $response)
    {
        try {
            $queueJob->setStatus(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW)
                     ->setResponse($response)
                     ->save();
            return $queueJob;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;

    }

    /**
     * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob
     * @param string $response
     * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface | boolean
     */
    public function markJobFailed(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob, string $response)
    {
        try {
            $queueJob->setStatus(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_FAILED)
                     ->setResponse($response)
                     ->save();
            return $queueJob;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;

    }
}
