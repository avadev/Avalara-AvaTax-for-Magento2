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

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;

use ClassyLlama\AvaTax\Api\Data;

class Response extends \Magento\Framework\DataObject implements Data\GetTaxResponseInterface
{
    /**
     * {@inheritDoc}
     */
    public function getIsUnbalanced()
    {
        return $this->getData(self::IS_UNBALANCED);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsUnbalanced($unbalanced)
    {
        $this->setData(self::IS_UNBALANCED, $unbalanced);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseAvataxTaxAmount() {
        return $this->getData(self::BASE_AVATAX_TAX_AMOUNT);
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseAvataxTaxAmount($amount) {
        $this->setData(self::BASE_AVATAX_TAX_AMOUNT, $amount);
        return $this;
    }
}
