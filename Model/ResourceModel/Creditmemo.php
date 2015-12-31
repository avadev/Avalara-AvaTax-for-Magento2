<?php

namespace ClassyLlama\AvaTax\Model\ResourceModel;

class Creditmemo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_creditmemo', 'id');
    }
}
