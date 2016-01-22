<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

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
