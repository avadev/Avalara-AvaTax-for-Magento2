<?php

namespace ClassyLlama\AvaTax\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;
use ClassyLlama\AvaTax\Api\Data\ValidAddressExtensionInterface;
use Magento\Quote\Api\Data\AddressInterface;

interface ValidAddressInterface extends CustomAttributesDataInterface
{
    const VALID_ADDRESS = 'valid_address';

    /**
     * Gets valid address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getValidAddress();

    /**
     * Sets valid address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setValidAddress(AddressInterface $address);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \ClassyLlama\AvaTax\Api\Data\ValidAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \ClassyLlama\AvaTax\Api\Data\ValidAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(ValidAddressExtensionInterface $extensionAttributes);
}
