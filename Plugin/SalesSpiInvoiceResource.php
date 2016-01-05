<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Config;

class SalesSpiInvoiceResource
{
    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \ClassyLlama\AvaTax\Model\InvoiceRepository
     */
    protected $avaTaxInvoiceRepository;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * @var \ClassyLlama\AvaTax\Api\Data\InvoiceInterfaceFactory
     */
    protected $avaTaxInvoiceFactory;

    /**
     * @param Config $avaTaxConfig
     * @param \ClassyLlama\AvaTax\Model\InvoiceRepository $avaTaxInvoiceRepository
     */
    public function __construct(
        Config $avaTaxConfig,
        \ClassyLlama\AvaTax\Model\InvoiceRepository $avaTaxInvoiceRepository,
        \Magento\Sales\Api\Data\InvoiceExtensionFactory $invoiceExtensionFactory,
        \ClassyLlama\AvaTax\Api\Data\InvoiceInterfaceFactory $avaTaxInvoiceFactory
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->avaTaxInvoiceRepository = $avaTaxInvoiceRepository;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->avaTaxInvoiceFactory = $avaTaxInvoiceFactory;
    }

    /**
     * @param \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $invoice
     * @return \Magento\Sales\Model\Spi\InvoiceResourceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $invoice
    ) {
        /** @var \Magento\Sales\Api\Data\InvoiceInterface $invoice */

        /** @var \Magento\Sales\Model\Spi\InvoiceResourceInterface $resultInvoice */
        $resultInvoice = $proceed($invoice);




        // Save AvaTax Invoice extension attributes

        // TODO: Check config to see if this should be enabled
        if (true == false)
        {
            return $resultInvoice;
        }

        // check to see if any extension attributes exist
        /* @var \Magento\Sales\Api\Data\InvoiceExtension $extensionAttributes */
        $extensionAttributes = $invoice->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $resultInvoice;
        }

        // check to see if any values are set on the avatax extension attributes
        $avataxInvoice = $extensionAttributes->getAvataxExtension();
        if ($avataxInvoice == null) {
            return $resultInvoice;
        }

        // save the AvaTax Invoice
        $this->avaTaxInvoiceRepository->save($avataxInvoice);

        return $resultInvoice;
    }

    /**
     * @param \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundLoad(
        \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $entity,
        $value,
        $field = null
    ) {
        /** @var \Magento\Sales\Api\Data\InvoiceInterface $entity */

        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resultEntity */
        $resultEntity = $proceed($entity, $value, $field);




        // Load AvaTax Invoice extension attributes

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
                $entityExtension = $this->invoiceExtensionFactory->create();
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
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Sales\Api\Data\InvoiceInterface $entity
     * @return \ClassyLlama\AvaTax\Api\Data\InvoiceInterface|null
     * @throws \Exception
     */
    protected function getAvaTaxEntity(\Magento\Framework\Model\AbstractModel $entity)
    {
        // Get the AvaTax Invoice
        try {
            return $this->avaTaxInvoiceRepository->getByEntityId($entity->getEntityId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // No entity found, create an empty one and return it
            return null;
        } catch (\Exception $e) {
            // TODO: Log the error as we should either be getting an entity back or not and creating an empty one
            throw $e;
        }
    }
}