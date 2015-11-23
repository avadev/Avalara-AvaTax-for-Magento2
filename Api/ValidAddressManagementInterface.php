<?php

namespace ClassyLlama\AvaTax\Api;

use ClassyLlama\AvaTax\Api\Data\ValidAddressInterface;

/**
 * Interface for managing valid address
 * @api
 */
interface ValidAddressManagementInterface
{
    /**
     * @param \ClassyLlama\AvaTax\Api\Data\ValidAddressInterface $validAddress
     * @return string[]
     */
    public function saveValidAddress(ValidAddressInterface $validAddress);
}
