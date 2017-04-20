<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Plugin\Sales\Model\Spi;

use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\QueueFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\CreditMemo;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Spi\CreditmemoResourceInterface;
use Magento\Framework\Model\AbstractModel;
use ClassyLlama\AvaTax\Model\ResourceModel\CreditMemo as CreditMemoResourceModel;

/**
 * Class CreditmemoResource
 */
class CreditmemoResource
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
     * @var \Magento\Sales\Api\Data\CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var CreditMemo
     */
    protected $avataxCreditMemo;

    /**
     * SalesSpiCreditmemoResource constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param CreditmemoExtensionFactory $creditmemoExtensionFactory
     * @param QueueFactory $queueFactory
     * @param DateTime $dateTime
     * @param CreditMemo $avataxCreditMemo
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        CreditmemoExtensionFactory $creditmemoExtensionFactory,
        QueueFactory $queueFactory,
        DateTime $dateTime,
        CreditMemo $avataxCreditMemo
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->queueFactory = $queueFactory;
        $this->dateTime = $dateTime;
        $this->avataxCreditMemo = $avataxCreditMemo;
    }

    /**
     * @param \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject
     * @param \Closure $proceed
     *
     *        I include both the extended AbstractModel and implemented Interface here for the IDE's benefit
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Sales\Model\Spi\CreditmemoResourceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        CreditmemoResourceInterface $subject,
        \Closure $proceed,
        AbstractModel $entity
    ) {
        // Check to see if this is a newly created entity and store the determination for later evaluation after
        // the entity is saved via plugin closure. After the entity is saved it will not be listed as new any longer.
        $isObjectNew = $entity->isObjectNew();

        /** @var \Magento\Sales\Model\Spi\CreditmemoResourceInterface $resultEntity */
        $resultEntity = $proceed($entity);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $entity->getOrder();
        $isVirtual = $order->getIsVirtual();
        $address = $isVirtual ? $entity->getBillingAddress() : $entity->getShippingAddress();
        $storeId = $entity->getStoreId();

        // Queue the entity to be sent to AvaTax
        if ($this->avaTaxConfig->isModuleEnabled($entity->getStoreId())
            && $this->avaTaxConfig->getTaxMode($entity->getStoreId()) == Config::TAX_MODE_ESTIMATE_AND_SUBMIT
            && $this->avaTaxConfig->isAddressTaxable($address, $storeId)
        ) {

            // Add this entity to the avatax processing queue if this is a new entity
            if ($isObjectNew) {
                /** @var Queue $queue */
                $queue = $this->queueFactory->create();
                $queue->build(
                    $entity->getStoreId(),
                    Queue::ENTITY_TYPE_CODE_CREDITMEMO,
                    $entity->getEntityId(),
                    $entity->getIncrementId(),
                    Queue::QUEUE_STATUS_PENDING
                );
                $queue->save();

                $this->avaTaxLogger->debug(
                    __('Added entity to the queue'),
                    [ /* context */
                        'queue_id' => $queue->getId(),
                        'entity_type_code' => Queue::ENTITY_TYPE_CODE_CREDITMEMO,
                        'entity_id' => $entity->getEntityId(),
                    ]
                );
            }
        }

        return $resultEntity;
    }

    /**
     * @param \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject
     * @param \Closure $proceed
     *
     *        Include both the extended AbstractModel and implemented Interface here for the IDE's benefit
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @param $value
     * @param null $field
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function aroundLoad(
        CreditmemoResourceInterface $subject,
        \Closure $proceed,
        AbstractModel $entity,
        $value,
        $field = null
    ) {
        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resultEntity */
        $resultEntity = $proceed($entity, $value, $field);

        // Load AvaTax record into extension attributes
        if ($this->avaTaxConfig->isModuleEnabled($entity->getStoreId())) {

            // Get the AvaTax record
            /** @var CreditMemo $avataxRecord */
            $avataxRecord = $this->avataxCreditMemo->load($entity->getId(), CreditMemoResourceModel::PARENT_ID_FIELD_NAME);
            $avataxIsUnbalanced = $avataxRecord->getIsUnbalanced();
            $baseAvataxTaxAmount = $avataxRecord->getBaseAvataxTaxAmount();

            // Check the AvaTax Entity to see if we need to add extension attributes
            if ($avataxIsUnbalanced !== null || $baseAvataxTaxAmount !== null) {
                // Get any existing extension attributes or create a new one
                $entityExtension = $entity->getExtensionAttributes();
                if (!$entityExtension) {
                    $entityExtension = $this->creditmemoExtensionFactory->create();
                }

                // Set the attributes
                if ($avataxIsUnbalanced !== null) {
                    $entityExtension->setAvataxIsUnbalanced($avataxIsUnbalanced);
                }
                if ($baseAvataxTaxAmount !== null) {
                    $entityExtension->setBaseAvataxTaxAmount($baseAvataxTaxAmount);
                }

                // save the ExtensionAttributes on the entity object
                $entity->setExtensionAttributes($entityExtension);
            }
        }

        return $resultEntity;
    }
}
