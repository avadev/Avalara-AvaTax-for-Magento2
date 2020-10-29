<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface CustomerUpdateInterface
 * @package ClassyLlama\AvaTax\Api
 */
interface CustomerUpdateInterface
{

    /**
     * Update customer information at the Avalara service
     *
     * @param int|null $customerId
     * @return CustomerUpdateInterface
     */
    public function updateCustomerInformation(int $customerId = null): CustomerUpdateInterface;
}
