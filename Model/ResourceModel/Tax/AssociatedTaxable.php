<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel\Tax;

use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AssociatedTaxable extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_associated_taxables', AssociatedTaxableInterface::ID);
    }
}
