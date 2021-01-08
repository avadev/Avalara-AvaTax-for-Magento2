<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface MultishippingAddressManagementInterface
 *
 * @package ClassyLlama\AvaTax\Api
 */
interface MultishippingAddressManagementInterface
{
    /**
     * @param Data\AddressInterface $address
     * @return bool
     */
    public function execute(Data\AddressInterface $address): bool;
}
