<?php

namespace ClassyLlama\AvaTax\Model;

/**
 * Queue
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Queue');
    }
}
