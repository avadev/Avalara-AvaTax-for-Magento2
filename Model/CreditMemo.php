<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

/**
 * CreditMemo
 *
 * @method int getParentId() getParentId()
 * @method string getIsUnbalanced() getIsUnbalanced()
 * @method float getBaseAvataxTaxAmount() getBaseAvataxTaxAmount()
 * @method CreditMemo setParentId() setParentId(int $parentId)
 * @method CreditMemo setIsUnbalanced() setIsUnbalanced(string $isUnbalanced)
 * @method CreditMemo setBaseAvataxTaxAmount() setBaseAvataxTaxAmount(float $baseAvataxTaxAmount)
 */
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
}
