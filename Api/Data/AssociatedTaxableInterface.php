<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api\Data;

interface AssociatedTaxableInterface
{
    const ID = 'id';
    const ORDER_ITEM_ID = 'order_item_id';
    const INVOICE_ID = 'invoice_id';
    const ORDER_ID = 'order_id';
    const CREDIT_MEMO_ID = 'credit_memo_id';
    const ITEM_ID = 'item_id';
    const TYPE = 'type';
    const CODE = 'code';
    const UNIT_PRICE = 'unit_price';
    const BASE_UNIT_PRICE = 'base_unit_price';
    const QUANTITY = 'quantity';
    const TAX_CLASS_ID = 'tax_class_id';
    const PRICE_INCLUDES_TAX = 'price_includes_tax';
    const ASSOCIATED_ITEM_CODE = 'associated_item_code';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return integer
     */
    public function getOrderItemId();

    /**
     * @param integer $orderItemId
     *
     * @return $this
     */
    public function setOrderItemId($orderItemId);

    /**
     * @return integer
     */
    public function getOrderId();

    /**
     * @param integer $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return integer
     */
    public function getInvoiceId();

    /**
     * @param integer $invoiceId
     *
     * @return $this
     */
    public function setInvoiceId($invoiceId);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code);

    /**
     * @return float
     */
    public function getUnitPrice();

    /**
     * @param float $unitPrice
     *
     * @return $this
     */
    public function setUnitPrice($unitPrice);

    /**
     * @return float
     */
    public function getBaseUnitPrice();

    /**
     * @param float $baseUnitPrice
     *
     * @return $this
     */
    public function setBaseUnitPrice($baseUnitPrice);

    /**
     * @return integer
     */
    public function getQuantity();

    /**
     * @param integer $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * @return string
     */
    public function getTaxClassId();

    /**
     * @param string $taxClassId
     *
     * @return $this
     */
    public function setTaxClassId($taxClassId);

    /**
     * @return bool
     */
    public function getPriceIncludesTax();

    /**
     * @param bool $priceIncludesTax
     *
     * @return $this
     */
    public function setPriceIncludesTax($priceIncludesTax);

    /**
     * @return string
     */
    public function getAssociatedItemCode();

    /**
     * @param string $associatedItemCode
     *
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode);
}
