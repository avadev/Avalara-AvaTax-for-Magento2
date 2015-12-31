<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class InvoiceRepository implements \ClassyLlama\AvaTax\Api\InvoiceRepositoryInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Invoice
     */
    protected $resource;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Invoice $resource
     * @param InvoiceFactory $invoiceFactory
     */
    public function __construct(
        \ClassyLlama\AvaTax\Model\ResourceModel\Invoice $resource,
        InvoiceFactory $invoiceFactory
    ) {
        $this->resource = $resource;
        $this->invoiceFactory = $invoiceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getByEntityId($entityId)
    {
        $entity = $this->invoiceFactory->create();
        $this->resource->load($entity, $entityId, Invoice::ENTITY_ID);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('AvaTax Invoice with id "%1" does not exist.', $entityId));
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \ClassyLlama\AvaTax\Api\Data\InvoiceInterface $invoice
    ) {
        /** @var \Magento\Framework\Model\AbstractModel $invoice */

        try {
            $this->resource->save($invoice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $invoice;
    }
}
