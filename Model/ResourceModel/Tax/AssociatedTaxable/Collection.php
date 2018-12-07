<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable;

use ClassyLlama\AvaTax\Model\Tax\AssociatedTaxable;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AssociatedTaxable::class, \ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable::class);
    }
}
