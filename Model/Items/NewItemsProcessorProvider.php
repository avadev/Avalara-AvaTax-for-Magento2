<?php

namespace ClassyLlama\AvaTax\Model\Items;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing;
use ClassyLlama\AvaTax\Model\Items\Processing\ProcessingStrategyInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class NewItemsProcessorProvider
 *
 * @package ClassyLlama\AvaTax\Model\Queue
 */
class NewItemsProcessorProvider
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
     * NewItemsProcessorProvider constructor.
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
        return $this->objectManager->create(BatchProcessing::class);
    }
}
