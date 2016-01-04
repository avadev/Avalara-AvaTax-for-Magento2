<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax;

use Magento\Backend\App\Action;

/**
 * Adminhtml controller
 */
abstract class Classes extends Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClassyLlama_AvaTax::manage_avatax');
    }
}
