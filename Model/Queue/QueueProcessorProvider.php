<?php

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Config\Source\QueueProcessingType;
use ClassyLlama\AvaTax\Model\Queue\Processing\BatchProcessing;
use ClassyLlama\AvaTax\Model\Queue\Processing\NormalProcessing;
use ClassyLlama\AvaTax\Model\Queue\Processing\ProcessingStrategyInterface;

/**
 * Class QueueProcessorProvider
 *
 * @package ClassyLlama\AvaTax\Model\Queue
 */
class QueueProcessorProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Processing\BatchProcessing
     */
    private $batchProcessing;

    /**
     * @var Processing\NormalProcessing
     */
    private $normalProcessing;

    /**
     * QueueProcessorProvider constructor.
     *
     * @param Config $config
     * @param BatchProcessing $batchProcessing
     * @param NormalProcessing $normalProcessing
     */
    public function __construct(
        Config $config,
        BatchProcessing $batchProcessing,
        NormalProcessing $normalProcessing
    ) {
        $this->config = $config;
        $this->batchProcessing = $batchProcessing;
        $this->normalProcessing = $normalProcessing;
    }

    /**
     * @return ProcessingStrategyInterface
     */
    public function getQueueProcessor(): ProcessingStrategyInterface
    {
        switch ($this->config->getQueueProcessingType()) {
            case QueueProcessingType::BATCH:
                $processor = $this->batchProcessing;
                break;
            case QueueProcessingType::NORMAL:
            default:
                $processor = $this->normalProcessing;
                break;
        }
        return $processor;
    }
}
