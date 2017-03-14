<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

class CreditMemo
    extends \Magento\Framework\Model\AbstractModel
    implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'classyllama_avatax_creditMemo';

    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\CreditMemo');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $creditMemoId
     * @return $this
     */
    public function loadByParentId($creditMemoId){
        if(!$creditMemoId){
            $creditMemoId = $this->getId();
        }
        $id = $this->getResource()->loadByParentId($creditMemoId);
        return $this->load($id);
    }
}
