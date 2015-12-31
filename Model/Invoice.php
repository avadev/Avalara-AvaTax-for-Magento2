<?php

namespace ClassyLlama\AvaTax\Model;

class Invoice extends \Magento\Framework\Model\AbstractModel implements \ClassyLlama\AvaTax\Api\Data\InvoiceInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Invoice');
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
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return Creditmemo
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->getData(self::ENTITY_TYPE_ID);
    }

    /**
     * @param int $entityTypeId
     * @return Creditmemo
     */
    public function setEntityTypeId($entityTypeId)
    {
        return $this->setData(self::ENTITY_TYPE_ID, $entityTypeId);
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getData(self::ENTITY_TYPE_CODE);
    }

    /**
     * @param string $entityTypeCode
     * @return Creditmemo
     */
    public function setEntityTypeCode($entityTypeCode)
    {
        return $this->setData(self::ENTITY_TYPE_CODE, $entityTypeCode);
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
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @param string $incrementId
     * @return Creditmemo
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
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