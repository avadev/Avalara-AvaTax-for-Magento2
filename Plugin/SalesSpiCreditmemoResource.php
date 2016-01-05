<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\QueueFactory;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

class SalesSpiCreditmemoResource
{
    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \ClassyLlama\AvaTax\Model\CreditmemoRepository
     */
    protected $avaTaxCreditmemoRepository;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    /**
     * @var \ClassyLlama\AvaTax\Api\Data\CreditmemoInterfaceFactory
     */
    protected $avaTaxCreditmemoFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param Config $avaTaxConfig
     * @param \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository,
        \Magento\Sales\Api\Data\CreditmemoExtensionFactory $creditmemoExtensionFactory,
        \ClassyLlama\AvaTax\Api\Data\CreditmemoInterfaceFactory $avaTaxCreditmemoFactory,
        QueueFactory $queueFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->avaTaxCreditmemoRepository = $avaTaxCreditmemoRepository;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->avaTaxCreditmemoFactory = $avaTaxCreditmemoFactory;
        $this->queueFactory = $queueFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $creditmemo
     * @return \Magento\Sales\Model\Spi\CreditmemoResourceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $entity
    ) {
        // Check to see if this is a newly created entity and store the determination for later evaluation after
        // the entity is saved via plugin closure. After the entity is saved is will not be listed as new any longer.
        $isObjectNew = $entity->isObjectNew();

        /** @var \Magento\Sales\Api\Data\CreditmemoInterface $entity */

        /** @var \Magento\Sales\Model\Spi\CreditmemoResourceInterface $resultEntity */
        $resultEntity = $proceed($entity);

        // Exit early if the AvaTax module is not enabled
        if ($this->avaTaxConfig->isModuleEnabled() == false)
        {
            return $resultEntity;
        }

        // Queue the entity to be sent to AvaTax
        if ($this->avaTaxConfig->getQueueSubmissionEnabled())
        {

            // Add this entity to the avatax processing queue if this is a new entity
            if ($isObjectNew)
            {
                //$entityTypeCode = $result->getEntityType();
                $entityType = $this->eavConfig->getEntityType(Queue::ENTITY_TYPE_CODE_CREDITMEMO);

                /** @var Queue $queue */
                $queue = $this->queueFactory->create();

                $queue->setData('store_id', $entity->getStoreId());
                $queue->setData('entity_type_id', $entityType->getEntityTypeId());
                $queue->setData('entity_type_code', Queue::ENTITY_TYPE_CODE_CREDITMEMO);
                $queue->setData('entity_id', $entity->getEntityId());
                $queue->setData('increment_id', $entity->getIncrementId());
                $queue->setData('queue_status', \ClassyLlama\AvaTax\Model\Queue::QUEUE_STATUS_PENDING);
                $queue->setData('attempts', 0);
                $queue->save();
            }
        }

        // Save AvaTax Credit Memo extension attributes

        // check to see if any extension attributes exist
        /* @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $resultEntity;

        }

        // check to see if any values are set on the avatax extension attributes
        $avataxCreditmemo = $extensionAttributes->getAvataxExtension();
        if ($avataxCreditmemo == null) {
            return $resultEntity;
        }

        // save the AvaTax Credit Memo
        $this->avaTaxCreditmemoRepository->save($avataxCreditmemo);

        return $resultEntity;
    }

    /**
     * @param \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $creditmemo
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundLoad(
        \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $entity,
        $value,
        $field = null
    ) {
        /** @var \Magento\Sales\Api\Data\CreditmemoInterface $entity */

        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resultEntity */
        $resultEntity = $proceed($entity, $value, $field);

        // Exit early if the AvaTax module is not enabled
        if ($this->avaTaxConfig->isModuleEnabled() == false)
        {
            return $resultEntity;
        }

        // Load AvaTax Invoice extension attributes

        // Get the AvaTax Entity
        $avaTaxEntity = $this->getAvaTaxEntity($entity);

        // Check the AvaTax Entity to see if we need to add extension attributes
        if ($avaTaxEntity != null)
        {
            // Get any existing extension attributes or create a new one
            $entityExtension = $entity->getExtensionAttributes();
            if ($entityExtension == null)
            {
                $entityExtension = $this->creditmemoExtensionFactory->create();
            }

            // check to see if the AvaTax Extension is already set on this entity
            if ($entityExtension->getAvataxExtension() == null)
            {
                // save the AvaTax Extension to the entityExtension object
                $entityExtension->setAvataxExtension($avaTaxEntity);

                // save the ExtensionAttributes on the entity object
                $entity->setExtensionAttributes($entityExtension);
            }
        }

        return $resultEntity;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \ClassyLlama\AvaTax\Api\Data\CreditmemoInterface|null
     * @throws \Exception
     */
    protected function getAvaTaxEntity(\Magento\Framework\Model\AbstractModel $entity)
    {
        // Get the AvaTax Entity
        try {
            return $this->avaTaxCreditmemoRepository->getByEntityId($entity->getEntityId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // No entity found, create an empty one and return it
            return null;
        } catch (\Exception $e) {
            // We should either be getting an entity back and returning it or a NoSuchEntityException and returning null

            // log warning
            $this->avaTaxLogger->error(
                'Attempting to get an AvaTax Credit Memo by the Entity ID returned an unexpected exception.',
                [ /* context */
                    'entity_id' => $entity->getEntityId(),
                    'entity_type_code' => Queue::ENTITY_TYPE_CODE_CREDITMEMO,
                    'exception_message' => $e->getMessage()
                ]
            );
            throw $e;
        }
    }
}