<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

use Magento\Backend\App\Action;
use ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

/**
 * Adminhtml controller
 */
abstract class Index extends Classes
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = null;

    /**
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('ClassyLlama_AvaTax::avatax_tax_classes_'  . \strtolower($this->classType));
        $pageResult->getConfig()->getTitle()->prepend(__(\ucfirst(\strtolower($this->classType)) . ' Tax Classes'));
        return $pageResult;
    }
}
