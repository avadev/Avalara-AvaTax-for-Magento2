<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Queue;

use ClassyLlama\AvaTax\Controller\Adminhtml\Queue;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

class Clear extends Queue
{
    /**
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // TODO: Clear queue records that have been completed
        $this->messageManager->addWarningMessage(__('TODO: You have reached code that has not been finished, and frankly does not do anything at this point. Congratulations!'));

        // Redirect browser to queue list page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
