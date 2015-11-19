<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Log;

class Index extends \ClassyLlama\AvaTax\Controller\Adminhtml\Log
{
    /**
     * Log page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('ClassyLlama_AvaTax::avatax_log');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('AvaTax Logs'));
        $this->_view->renderLayout();
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClassyLlama_AvaTax::manage_avatax');
    }
}
