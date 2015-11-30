<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface for managing valid address
 * @api
 */
interface ValidAddressManagementInterface
{
    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return \Magento\Quote\Api\Data\AddressInterface|string
     */
    public function saveValidAddress(\Magento\Quote\Api\Data\AddressInterface $address);
}
