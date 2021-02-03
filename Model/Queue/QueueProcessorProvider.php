<?php

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Queue\Processing\NormalProcessing;
use ClassyLlama\AvaTax\Model\Queue\Processing\ProcessingStrategyInterface;
use Magento\Framework\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $processors;

    /**
     * QueueProcessorProvider constructor.
     *
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     * @param array $processors
     */
    public function __construct(
        Config $config,
        ObjectManagerInterface $objectManager,
        $processors = []
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->processors = $processors;
    }

    /**
     * @return ProcessingStrategyInterface
     */
    public function getQueueProcessor(): ProcessingStrategyInterface
    {
        return isset($this->processors[$this->config->getQueueProcessingType()])
            ? $this->objectManager->create($this->processors[$this->config->getQueueProcessingType()])
            : $this->objectManager->create(NormalProcessing::class);
    }
}
