<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel\CreditMemo;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\CreditMemo', 'ClassyLlama\AvaTax\Model\ResourceModel\CreditMemo');
    }
}
