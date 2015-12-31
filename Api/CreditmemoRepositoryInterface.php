<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface CreditmemoRepositoryInterface
 * @api
 */
interface CreditmemoRepositoryInterface
{
    /**
     * Get AvaTax Credit Memo
     *
     * @param int $entityId
     * @return \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface
     */
    public function getByEntityId($entityId);

    /**
     * Add new AvaTax Credit Memo
     *
     * @param \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface $creditmemo
     * @return \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface $creditmemo
    );
}
