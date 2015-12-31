<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CreditmemoRepository implements \ClassyLlama\AvaTax\Api\CreditmemoRepositoryInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Creditmemo
     */
    protected $resource;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Creditmemo $resource
     * @param CreditmemoFactory $creditmemoFactory
     */
    public function __construct(
        \ClassyLlama\AvaTax\Model\ResourceModel\Creditmemo $resource,
        CreditmemoFactory $creditmemoFactory
    ) {
        $this->resource = $resource;
        $this->creditmemoFactory = $creditmemoFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getByEntityId($entityId)
    {
        $entity = $this->creditmemoFactory->create();
        $this->resource->load($entity, $entityId, CreditMemo::ENTITY_ID);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('AvaTax Credit Memo with id "%1" does not exist.', $entityId));
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface $creditmemo
    ) {
        /** @var \Magento\Framework\Model\AbstractModel $creditmemo */

        try {
            $this->resource->save($creditmemo);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $creditmemo;
    }
}
