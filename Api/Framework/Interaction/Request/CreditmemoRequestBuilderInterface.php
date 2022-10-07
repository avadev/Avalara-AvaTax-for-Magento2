<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Request;

use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Interface CreditmemoRequestBuilderInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Request
 */
interface CreditmemoRequestBuilderInterface
{

    /**
     * Creditmemo request builder
     *
     * @param CreditmemoInterface $creditmemo
     * @return RequestInterface
     * @throws \Throwable
     */
    public function build(CreditmemoInterface $creditmemo): RequestInterface;
}
