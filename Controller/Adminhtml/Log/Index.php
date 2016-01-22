<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Log;

use ClassyLlama\AvaTax\Controller\Adminhtml\Log;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends Log
{
    /**
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('ClassyLlama_AvaTax::avatax_log');
        $pageResult->getConfig()->getTitle()->prepend(__('AvaTax Logs'));
        return $pageResult;
    }
}
