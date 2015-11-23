<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\AvaTax\Model\AvaTax;

use Magento\Framework\Model\AbstractExtensibleModel;
use ClassyLlama\AvaTax\Api\Data\ValidAddressInterface;
use ClassyLlama\AvaTax\Api\Data\ValidAddressExtensionInterface;
use Magento\Quote\Api\Data\AddressInterface;

class ValidAddress extends AbstractExtensibleModel implements ValidAddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValidAddress()
    {
        return $this->getData(self::VALID_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setValidAddress(AddressInterface $address)
    {
        return $this->setData(self::VALID_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ValidAddressExtensionInterface $extensionAttributes) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
