<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Data\Collection;

class ExtensionAttributeCollectionLoader
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     */
    public function __construct(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor)
    {
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $subject
     * @param bool                                          $printQuery
     * @param bool                                          $logQuery
     */
    public function beforeLoad(
        \Magento\Framework\Data\Collection\AbstractDb $subject,
        $printQuery = false,
        $logQuery = false
    )
    {
        $this->joinProcessor->process($subject);
    }
}