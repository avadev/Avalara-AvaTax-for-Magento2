<?php

namespace ClassyLlama\AvaTax\Model\ResourceModel\Tax\Classes;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Customer
 */
class Customer extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tax_class', 'class_id');
    }
}
