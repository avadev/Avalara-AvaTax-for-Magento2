<?php

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Log
 */
class Log extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_log', 'log_id');
    }
}
