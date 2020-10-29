<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Request;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;

/**
 * Interface LineBuilderInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Request
 */
interface LineBuilderInterface
{

    /**
     * Line builder
     *
     * @param CreditmemoInterface $creditmemo
     * @param OrderItem[] $orderItems
     * @param bool $flag
     * @return array
     * @throws ValidationException
     */
    public function build(CreditmemoInterface $creditmemo, array $orderItems, bool $flag): array;
}
