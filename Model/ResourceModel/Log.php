<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Log
 */
class Log extends AbstractDb
{
    /**#@+
     * Field Names
     */
    const LEVEL_FIELD_NAME = 'level';
    const CREATED_AT_FIELD_NAME = 'created_at';
    /**#@-*/

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
