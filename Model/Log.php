<?php

namespace ClassyLlama\AvaTax\Model;

/**
 * Log
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Log');
    }
}
