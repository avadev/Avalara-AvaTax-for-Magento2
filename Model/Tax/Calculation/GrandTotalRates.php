<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Tax\Calculation;

use \Magento\Framework\Api\ExtensibleDataInterface;

class GrandTotalRates
    extends \Magento\Tax\Model\Calculation\GrandTotalRates
    implements \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesInterface
{
    /**
     * {@inheritdoc}
     *
     * @return \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_get(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     *
     * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    ) {
        $this->_data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] = $extensionAttributes;
        return $this;
    }
}
