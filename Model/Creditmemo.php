<?php

namespace ClassyLlama\AvaTax\Model;

class Creditmemo extends \Magento\Framework\Model\AbstractModel implements \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Creditmemo');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $id
     * @return Creditmemo
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     * @return Creditmemo
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get whether Magento's tax amount matches AvaTax's tax calculation
     *
     * @return bool
     */
    public function getIsUnbalanced()
    {
        return $this->getData(self::IS_UNBALANCED);
    }

    /**
     * Set whether Magento's tax amount matches AvaTax's tax calculation
     *
     * @param bool $unbalanced
     * @return Creditmemo
     */
    public function setIsUnbalanced($unbalanced)
    {
        return $this->setData(self::IS_UNBALANCED, $unbalanced);
    }

    /**
     * Get tax amount that AvaTax calculated for this response
     *
     * @return float
     */
    public function getBaseAvataxTaxAmount()
    {
        return $this->getData(self::BASE_AVATAX_TAX_AMOUNT);
    }

    /**
     * Set tax amount that AvaTax calculated for this response
     *
     * @param float $amount
     * @return Creditmemo
     */
    public function setBaseAvataxTaxAmount($amount)
    {
        return $this->setData(self::BASE_AVATAX_TAX_AMOUNT, $amount);
    }
}