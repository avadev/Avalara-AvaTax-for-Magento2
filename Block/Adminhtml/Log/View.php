<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Log;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Form widget for viewing log
 */
class View extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get log model
     *
     * @return \ClassyLlama\AvaTax\Model\Log
     */
    public function getLog()
    {
        return $this->coreRegistry->registry('current_log');
    }
}
