<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface for managing valid address
 * @api
 */
interface ValidAddressManagementInterface
{
    /**
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface|string
     */
    public function saveValidAddress(\Magento\Customer\Api\Data\AddressInterface $address);
}
