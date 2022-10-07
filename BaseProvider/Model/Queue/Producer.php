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
use ClassyLlama\AvaTax\BaseProvider\Model\QueueFactory as QueueFactory;

class Producer
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var QueueConfig
     */
    protected $queueConfig;

    /**
     * @param LoggerInterface 
     * @param QueueConfig $queueConfig
     * @param QueueFactory $queueFactory
     * @param array $processors
     */
    public function __construct(
        LoggerInterface $logger,
        QueueConfig $queueConfig,
        QueueFactory $queueFactory
    ) {
        $this->logger = $logger;
        $this->queueConfig = $queueConfig;
        $this->queueFactory = $queueFactory;
    }

    /**
     * Add queue job
     *
     * @param string $client
     * @param string $payload
     * @return boolean
     */
    public function addJob($client, $payload)
    {
        try {
            $queue = $this->queueFactory->create();
            $queue->setClient($client)
                ->setPayload($payload)
                ->setStatus(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::STATUS_NEW)
                ->setAttempt(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface::MIN_ATTEMPT)
                ->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
			throw $e;
        }
        return false;
    }
}
