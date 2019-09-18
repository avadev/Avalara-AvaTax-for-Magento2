<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Request;

use Magento\Sales\Model\Order;

/**
 * Interface AddressBuilderInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Request
 */
interface AddressBuilderInterface
{

    /**
     * Address builder
     *
     * @param Order $order
     * @param int $storeId
     * @return array
     */
    public function build(Order $order, int $storeId): array;
}
