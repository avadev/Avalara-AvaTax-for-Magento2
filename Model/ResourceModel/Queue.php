<?php

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Queue
 */
class Queue extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_queue', 'queue_id');
    }
}
