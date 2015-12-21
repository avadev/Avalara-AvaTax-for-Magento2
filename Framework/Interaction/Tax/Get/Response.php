<?php

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
