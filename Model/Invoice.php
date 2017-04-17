<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

/**
 * Invoice
 *
 * @method int getParentId() getParentId()
 * @method string getIsUnbalanced() getIsUnbalanced()
 * @method float getBaseAvataxTaxAmount() getBaseAvataxTaxAmount()
 * @method Invoice setParentId() setParentId(int $parentId)
 * @method Invoice setIsUnbalanced() setIsUnbalanced(string $isUnbalanced)
 * @method Invoice setBaseAvataxTaxAmount() setBaseAvataxTaxAmount(float $baseAvataxTaxAmount)
 */
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
}
