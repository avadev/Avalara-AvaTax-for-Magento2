<?php

namespace ClassyLlama\AvaTax\Model\Items;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Items\Processing\PendingItemsProcessing;
use ClassyLlama\AvaTax\Model\Items\Processing\ProcessingStrategyInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class PendingItemsProcessorProvider
 *
 * @package ClassyLlama\AvaTax\Model\Queue
 */
class PendingItemsProcessorProvider
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
     * PendingItemsProcessorProvider constructor.
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
    public function getItemsProcessor(): ProcessingStrategyInterface
    {
        return $this->objectManager->create(PendingItemsProcessing::class);
    }
}
