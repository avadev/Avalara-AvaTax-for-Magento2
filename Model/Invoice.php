<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

class Invoice
    extends \Magento\Framework\Model\AbstractModel
    implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'classyllama_avatax_invoice';

    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Invoice');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $invoiceId
     * @return $this
     */
    public function loadByParentId($invoiceId)
    {
        if(!$invoiceId){
            $invoiceId = $this->getId();
        }
        $id = $this->getResource()->loadByParentId($invoiceId);
        return $this->load($id);
    }
}
