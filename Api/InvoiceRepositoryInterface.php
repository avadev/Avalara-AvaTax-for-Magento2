<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface InvoiceRepositoryInterface
 * @api
 */
interface InvoiceRepositoryInterface
{
    /**
     * Get AvaTax Invoice
     *
     * @param int $entityId
     * @return \ClassyLlama\AvaTax\Api\Data\InvoiceInterface
     */
    public function getByEntityId($entityId);

    /**
     * Add new AvaTax Invoice
     *
     * @param \ClassyLlama\AvaTax\Api\Data\InvoiceInterface $invoice
     * @return \ClassyLlama\AvaTax\Api\Data\InvoiceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \ClassyLlama\AvaTax\Api\Data\InvoiceInterface $invoice
    );
}
