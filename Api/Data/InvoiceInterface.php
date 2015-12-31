<?php

namespace ClassyLlama\AvaTax\Api\Data;

/**
 * @api
 */
interface InvoiceInterface
{
    /**#@+
     * Array keys
     */
    const ID = 'id';

    const STORE_ID = 'store_id';

    const ENTITY_TYPE_ID = 'entity_type_id';

    const ENTITY_TYPE_CODE = 'entity_type_code';

    const ENTITY_ID = 'entity_id';

    const INCREMENT_ID = 'increment_id';

    const IS_UNBALANCED = 'is_unbalanced';

    const BASE_AVATAX_TAX_AMOUNT = 'base_avatax_tax_amount';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getEntityTypeId();

    /**
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId);

    /**
     * @return string
     */
    public function getEntityTypeCode();

    /**
     * @param string $entityTypeCode
     * @return $this
     */
    public function setEntityTypeCode($entityTypeCode);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getIncrementId();

    /**
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Get whether Magento's tax amount matches AvaTax's tax calculation
     *
     * @return bool
     */
    public function getIsUnbalanced();

    /**
     * Set whether Magento's tax amount matches AvaTax's tax calculation
     *
     * @param bool $unbalanced
     * @return $this
     */
    public function setIsUnbalanced($unbalanced);

    /**
     * Get tax amount that AvaTax calculated for this response
     *
     * @return float
     */
    public function getBaseAvataxTaxAmount();

    /**
     * Set tax amount that AvaTax calculated for this response
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseAvataxTaxAmount($amount);
}
