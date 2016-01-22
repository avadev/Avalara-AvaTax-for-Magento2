<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

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
