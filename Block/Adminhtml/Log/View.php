<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Log;

/**
 * Form widget for viewing report
 */
class View extends \Magento\Backend\Block\Widget\Container
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
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get current report model
     *
     * @return \Magento\Support\Model\Report
     */
    public function getLog()
    {
        return $this->coreRegistry->registry('current_log');
    }
}
