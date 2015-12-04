<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Adminhtml AvaTax log controller
 */
abstract class Queue extends Action
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
