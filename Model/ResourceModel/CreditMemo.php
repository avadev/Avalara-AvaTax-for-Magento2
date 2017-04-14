<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel;

class CreditMemo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**#@+
     * Field Names
     */
    const ENTITY_ID_FIELD_NAME = 'entity_id';
    const PARENT_ID_FIELD_NAME = 'parent_id';
    const IS_UNBALANCED_FIELD_NAME = 'is_unbalanced';
    const TAX_AMOUNT_FIELD_NAME = 'base_avatax_tax_amount';
    /**#@-*/

    protected function _construct()
    {
        $this->_init('avatax_sales_creditmemo', 'entity_id');
    }
}
