<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

/**
 * @codeCoverageIgnore
 */
abstract class ClassesAbstract extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClassyLlama_AvaTax::manage_crossborder_classes');
    }

    /**
     * Index page for managing cross border classes
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('ClassyLlama_AvaTax::avatax_cross_border_classes');
        $pageResult->getConfig()->getTitle()->prepend(__('AvaTax Cross Border Classes'));
        return $pageResult;
    }
}
