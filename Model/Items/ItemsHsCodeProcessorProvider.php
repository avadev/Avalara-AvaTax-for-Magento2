<?php

namespace ClassyLlama\AvaTax\Model\Items;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Items\Processing\HsCodeBatchProcessing;
use ClassyLlama\AvaTax\Model\Items\Processing\ProcessingStrategyInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ItemsHsCodeProcessorProvider
 *
 * @package ClassyLlama\AvaTax\Model\Queue
 */
class ItemsHsCodeProcessorProvider
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
     * ItemsHsCodeProcessorProvider constructor.
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
    public function getItemsHsCodeProcessor(): ProcessingStrategyInterface
    {
        return $this->objectManager->create(HsCodeBatchProcessing::class);
    }
}
