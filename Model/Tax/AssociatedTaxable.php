<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Tax;

use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterface;

class AssociatedTaxable extends \Magento\Framework\Model\AbstractModel implements AssociatedTaxableInterface
{

    public function _construct()
    {
        $this->_init(\ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getData(AssociatedTaxableInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItemId()
    {
        return $this->_getData(AssociatedTaxableInterface::ORDER_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->_getData(AssociatedTaxableInterface::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvoiceId()
    {
        return $this->_getData(AssociatedTaxableInterface::INVOICE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->_getData(AssociatedTaxableInterface::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_getData(AssociatedTaxableInterface::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_getData(AssociatedTaxableInterface::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        return $this->_getData(AssociatedTaxableInterface::UNIT_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUnitPrice()
    {
        return $this->_getData(AssociatedTaxableInterface::BASE_UNIT_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuantity()
    {
        return $this->_getData(AssociatedTaxableInterface::QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId()
    {
        return $this->_getData(AssociatedTaxableInterface::TAX_CLASS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceIncludesTax()
    {
        return $this->_getData(AssociatedTaxableInterface::PRICE_INCLUDES_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->_getData(AssociatedTaxableInterface::ASSOCIATED_ITEM_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoId()
    {
        return $this->_getData(AssociatedTaxableInterface::CREDIT_MEMO_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($orderItemId)
    {
        return $this->setData(AssociatedTaxableInterface::ORDER_ITEM_ID, $orderItemId);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(AssociatedTaxableInterface::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvoiceId($invoiceId)
    {
        return $this->setData(AssociatedTaxableInterface::INVOICE_ID, $invoiceId);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(AssociatedTaxableInterface::ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(AssociatedTaxableInterface::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(AssociatedTaxableInterface::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setUnitPrice($unitPrice)
    {
        return $this->setData(AssociatedTaxableInterface::UNIT_PRICE, $unitPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUnitPrice($baseUnitPrice)
    {
        return $this->setData(AssociatedTaxableInterface::BASE_UNIT_PRICE, $baseUnitPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuantity($quantity)
    {
        return $this->setData(AssociatedTaxableInterface::QUANTITY, $quantity);
    }

    /**
     * {@inheritdoc}
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(AssociatedTaxableInterface::TAX_CLASS_ID, $taxClassId);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceIncludesTax($priceIncludesTax)
    {
        return $this->setData(AssociatedTaxableInterface::PRICE_INCLUDES_TAX, $priceIncludesTax);
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociatedItemCode($associatedItemCode)
    {
        return $this->setData(AssociatedTaxableInterface::ASSOCIATED_ITEM_CODE, $associatedItemCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditMemoId($creditMemoId)
    {
        return $this->setData(AssociatedTaxableInterface::CREDIT_MEMO_ID, $creditMemoId);
    }
}
