<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\Config;

class SalesSpiCreditmemoResource
{
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
     * @param Config $avaTaxConfig
     * @param \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository
     */
    public function __construct(
        Config $avaTaxConfig,
        \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository,
        \Magento\Sales\Api\Data\CreditmemoExtensionFactory $creditmemoExtensionFactory,
        \ClassyLlama\AvaTax\Api\Data\CreditmemoInterfaceFactory $avaTaxCreditmemoFactory
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->avaTaxCreditmemoRepository = $avaTaxCreditmemoRepository;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->avaTaxCreditmemoFactory = $avaTaxCreditmemoFactory;
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
        \Magento\Framework\Model\AbstractModel $creditmemo
    ) {
        /** @var \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo */

        /** @var \Magento\Sales\Model\Spi\CreditmemoResourceInterface $resultCreditmemo */
        $resultCreditmemo = $proceed($creditmemo);




        // Save AvaTax Credit Memo extension attributes

        // TODO: Check config to see if this should be enabled
        if (true == false)
        {
           return $resultCreditmemo;
        }

        // check to see if any extension attributes exist
        /* @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $creditmemo->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $resultCreditmemo;

        }

        // check to see if any values are set on the avatax extension attributes
        $avataxCreditmemo = $extensionAttributes->getAvataxExtension();
        if ($avataxCreditmemo == null) {
            return $resultCreditmemo;
        }

        // save the AvaTax Credit Memo
        $this->avaTaxCreditmemoRepository->save($avataxCreditmemo);

        return $resultCreditmemo;
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




        // Load AvaTax Credit Memo extension attributes

        // TODO: Check config to see if this should be enabled
        if (true == false)
        {
            return $resultEntity;
        }

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
        // Get the AvaTax Invoice
        try {
            return $this->avaTaxCreditmemoRepository->getByEntityId($entity->getEntityId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // No entity found, create an empty one and return it
            return null;
        } catch (\Exception $e) {
            // TODO: Log the error as we should either be getting an entity back or not and creating an empty one
            throw $e;
        }
    }
}