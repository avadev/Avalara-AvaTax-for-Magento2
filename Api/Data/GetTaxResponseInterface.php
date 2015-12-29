<?php

namespace ClassyLlama\AvaTax\Api\Data;

/**
 * @api
 */
interface GetTaxResponseInterface
{
    /**#@+
     * Array keys
     */
    const IS_UNBALANCED = 'is_unbalanced';

    const BASE_AVATAX_TAX_AMOUNT = 'base_avatax_tax_amount';
    /**#@-*/

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
