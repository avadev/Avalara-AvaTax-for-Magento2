<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;
use ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Queue Processing
 */
class Processing
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
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    protected $orderStatusHistoryFactory;

    /**
     * @var InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * @var CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * Processing constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param Get $interactionGetTax
     * @param DateTime $dateTime
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
     * @param InvoiceExtensionFactory $invoiceExtensionFactory
     * @param CreditmemoExtensionFactory $creditmemoExtensionFactory
     * @param EavConfig $eavConfig
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        Get $interactionGetTax,
        DateTime $dateTime,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        InvoiceExtensionFactory $invoiceExtensionFactory,
        CreditmemoExtensionFactory $creditmemoExtensionFactory,
        EavConfig $eavConfig
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->interactionGetTax = $interactionGetTax;
        $this->dateTime = $dateTime;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Execute processing of the queued entity
     *
     * @param Queue $queue
     * @throws \Exception
     */
    public function execute(Queue $queue)
    {
        // Initialize the queue processing
        // Check for valid queue status that allows processing
        // Update queue status and attempts on this record
        $this->initializeQueueProcessing($queue);

        // Get the credit memo or invoice entity
        $entity = $this->getProcessingEntity($queue);

        // Process entity with AvaTax
        $processSalesResponse = $this->processWithAvaTax($queue, $entity);

        // update invoice with additional fields
        $this->updateAdditionalEntityAttributes($entity, $processSalesResponse);

        // Update the queue record status
        // and add comment to order
        $this->completeQueueProcessing($queue, $entity, $processSalesResponse);
    }

    /**
     * @param Queue $queue
     * @throws \Exception
     */
    protected function initializeQueueProcessing(Queue $queue)
    {
        // validity check
        if ($queue->getQueueStatus() == Queue::QUEUE_STATUS_COMPLETE) {
            // We should not be attempting to process queue records that have already been marked as complete

            // log warning
            $this->avaTaxLogger->warning(
                __('Processing was attempted on a queue record that has already been processed and marked as completed.'),
                [ /* context */
                    'queue_id' => $queue->getId(),
                    'entity_type_code' => $queue->getEntityTypeCode(),
                    'increment_id' => $queue->getIncrementId(),
                    'queue_status' => $queue->getQueueStatus(),
                    'updated_at' => $queue->getUpdatedAt()
                ]
            );

            throw new \Exception(__('The queue record has already been processed, and the queue record marked as complete'));
        }

        // update queue record with new processing status
        $queue->setQueueStatus(Queue::QUEUE_STATUS_PROCESSING);

        // update queue incrementing attempts
        $queue->setAttempts($queue->getAttempts()+1);

        /* @var $queueResource \ClassyLlama\AvaTax\Model\ResourceModel\Queue */
        $queueResource = $queue->getResource();
        $changedResult = $queueResource->changeQueueStatusWithLocking($queue);

        if (!$changedResult) {
            // Something else has modified the queue record, skip processing

            // This indicates something intercepted the queue record and changed its status
            // before we were able to process it, like some other process was also attempting to
            // process queue records. We prefer not to send duplicates to AvaTax.

            // log warning
            $this->avaTaxLogger->warning(
                __('The queue status changed while attempting to process it. This could indicate multiple processes' .
                'attempting to process the same queue record at the same time.'),
                [ /* context */
                    'queue_id' => $queue->getId(),
                    'entity_type_code' => $queue->getEntityTypeCode(),
                    'increment_id' => $queue->getIncrementId(),
                    'queue_status' => $queue->getQueueStatus(),
                    'updated_at' => $queue->getUpdatedAt()
                ]
            );

            throw new \Exception(__('Something else has modified the queue record, skip processing'));
        }
    }

    /**
     * Process invoice or credit memo
     *
     * @param Queue $queue
     * @return \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface
     * @throws \Exception
     */
    protected function getProcessingEntity(Queue $queue)
    {
        // Check to see which type of entity we are processing
        if ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_INVOICE) {

            try {
                /* @var $invoice \Magento\Sales\Api\Data\InvoiceInterface */
                $invoice = $this->invoiceRepository->get($queue->getEntityId());
                if ($invoice->getEntityId()) {
                    return $invoice;
                } else {
                    $message = __('Invoice not found: (EntityId: %1, IncrementId: %2)',
                        $queue->getEntityId(),
                        $queue->getIncrementId()
                    );

                    // Update the queue record
                    $this->failQueueProcessing($queue, $message);

                    throw new \Exception($message);
                }
            } catch (NoSuchEntityException $e) {
                /* @var $message \Magento\Framework\Phrase */
                $message = __('Queue ID: %1 - Invoice not found: (EntityId: %2, IncrementId: %3)',
                    $queue->getId(),
                    $queue->getEntityId(),
                    $queue->getIncrementId()
                );

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw new NoSuchEntityException($message, $e);
            } catch (\Exception $e) {
                $message = __('Unexpected Exception getProcessingEntity() invoiceRepository->get(): ') . "\n" .
                    $e->getMessage() . "\n" .
                    $queue->getMessage();

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw new \Exception($message);
            }
        } elseif ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_CREDITMEMO) {

            try {

                /* @var $creditmemo \Magento\Sales\Api\Data\CreditmemoInterface */
                $creditmemo = $this->creditmemoRepository->get($queue->getEntityId());
                if ($creditmemo->getEntityId()) {
                    return $creditmemo;
                } else {
                    $message = __('Credit Memo not found: (EntityId: %1, IncrementId: %2)',
                        $queue->getEntityId(),
                        $queue->getIncrementId()
                    );

                    // Update the queue record
                    $this->failQueueProcessing($queue, $message);

                    throw new \Exception($message);
                }
            } catch (\Exception $e) {
                $message = __('ERROR getProcessingEntity() creditmemoRepository->get(): ') . "\n" .
                    $e->getMessage() . "\n" .
                    $queue->getMessage();

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw $e;
            }
        } else {
            $message = __('Unknown Entity Type Code for processing (%1)', $queue->getEntityTypeCode());

            // Update the queue record
            $this->failQueueProcessing($queue, $message);

            throw new \Exception();
        }
    }

    /**
     * @param Queue $queue
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface
     * @throws \Exception
     */
    protected function processWithAvaTax(Queue $queue, $entity)
    {
        try {
            $processSalesResponse = $this->interactionGetTax->processSalesObject($entity);

        } catch (\Exception $e) {

            $message = '';
            if ($e instanceof \ClassyLlama\AvaTax\Exception\TaxCalculationException) {
                $message .= __('An error occurred when attempting to send %1 #%2 to AvaTax.',
                    ucfirst($queue->getEntityTypeCode()),
                    $entity->getIncrementId()
                );
            } else {
                $message .= __('An unexpected exception occurred when attempting to send %1 #%2 to AvaTax.',
                    ucfirst($queue->getEntityTypeCode()),
                    $entity->getIncrementId()
                );
            }

            // Log the error
            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                    'queue_id' => $queue->getId(),
                    'entity_type_code' => $queue->getEntityTypeCode(),
                    'increment_id' => $queue->getIncrementId(),
                    'exception' => sprintf(
                        'Exception message: %s%sTrace: %s',
                        $e->getMessage(),
                        "\n",
                        $e->getTraceAsString()
                    ),
                ]
            );

            // Update the queue record
            // and add comment to order
            $this->resetQueueingForProcessing($queue, $message, $entity);

            throw new \Exception($message, null, $e);
        }

        return $processSalesResponse;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @param \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface $processSalesResponse
     */
    protected function updateAdditionalEntityAttributes(
        $entity,
        GetTaxResponseInterface $processSalesResponse
    ) {
        $entityExtension = $entity->getExtensionAttributes();
        if ($entityExtension == null) {
            $entityExtension = $this->getEntityExtensionInterface($entity);
        }

        // check to see if the AvataxIsUnbalanced is already set on this entity
        $avataxIsUnbalancedToSave = false;
        if ($entityExtension->getAvataxIsUnbalanced() === null) {
            $entityExtension->setAvataxIsUnbalanced($processSalesResponse->getIsUnbalanced());
            $avataxIsUnbalancedToSave = true;
        } else {
            // check to see if any existing value is different from the new value
            if ($processSalesResponse->getIsUnbalanced() <> $entityExtension->getAvataxIsUnbalanced()) {
                // Log the warning
                $this->avaTaxLogger->warning(
                    __('When processing an entity in the queue there was an existing AvataxIsUnbalanced and ' .
                    'the new value was different than the old one. The old value was overwritten.'),
                    [ /* context */
                        'old_is_unbalanced' => $entityExtension->getAvataxIsUnbalanced(),
                        'new_is_unbalanced' => $processSalesResponse->getIsUnbalanced(),
                    ]
                );
                $entityExtension->setAvataxIsUnbalanced($processSalesResponse->getIsUnbalanced());
                $avataxIsUnbalancedToSave = true;
            }
        }

        // check to see if the BaseAvataxTaxAmount is already set on this entity
        $baseAvataxTaxAmountToSave = false;
        if ($entityExtension->getBaseAvataxTaxAmount() === null) {
            $entityExtension->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
            $baseAvataxTaxAmountToSave = true;
        } else {
            // check to see if any existing value is different from the new value
            if ($processSalesResponse->getBaseAvataxTaxAmount() <> $entityExtension->getBaseAvataxTaxAmount()) {
                // Log the warning
                $this->avaTaxLogger->warning(
                    __('When processing an entity in the queue there was an existing BaseAvataxTaxAmount and ' .
                    'the new value was different than the old one. The old value was overwritten.'),
                    [ /* context */
                        'old_base_avatax_tax_amount' => $entityExtension->getBaseAvataxTaxAmount(),
                        'new_base_avatax_tax_amount' => $processSalesResponse->getBaseAvataxTaxAmount(),
                    ]
                );
                $entityExtension->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
                $baseAvataxTaxAmountToSave = true;
            }
        }

        // save the ExtensionAttributes on the entity object
        if ($avataxIsUnbalancedToSave || $baseAvataxTaxAmountToSave) {
            $entity->setExtensionAttributes($entityExtension);

            // get the repository for this entity type
            $entityRepository = $this->getEntityRepository($entity);

            // save the entity object using the repository
            $entityRepository->save($entity);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Sales\Api\Data\InvoiceExtension|\Magento\Sales\Api\Data\CreditmemoExtension
     * @throws \Exception
     */
    protected function getEntityExtensionInterface($entity)
    {
        if ($entity instanceof InvoiceInterface) {
            return $this->invoiceExtensionFactory->create();
        } elseif ($entity instanceof CreditmemoInterface) {
            return $this->creditmemoExtensionFactory->create();
        } else {
            $message = __('Did not receive a valid entity instance to determine the extension to return');
            throw new \Exception($message);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Eav\Model\Entity\Type
     * @throws \Exception
     */
    protected function getEntityType($entity)
    {
        if ($entity instanceof InvoiceInterface) {
            return $this->eavConfig->getEntityType(Queue::ENTITY_TYPE_CODE_INVOICE);
        } elseif ($entity instanceof CreditmemoInterface) {
            return $this->eavConfig->getEntityType(Queue::ENTITY_TYPE_CODE_CREDITMEMO);
        } else {
            $message = __('Did not receive a valid entity instance to determine the entity type to return');
            throw new \Exception($message);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|\Magento\Sales\Api\CreditmemoRepositoryInterface
     * @throws \Exception
     */
    protected function getEntityRepository($entity)
    {
        if ($entity instanceof InvoiceInterface) {
            return $this->invoiceRepository;
        } elseif ($entity instanceof CreditmemoInterface) {
            return $this->creditmemoRepository;
        } else {
            $message = __('Did not receive a valid entity instance to determine the repository type to return');
            throw new \Exception($message);
        }
    }

    /**
     * Set queue to failed
     *
     * @param Queue $queue
     * @param string $message
     */
    protected function failQueueProcessing(Queue $queue, $message)
    {
        $queue->setMessage($message);
        $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED);
        $queue->save();
    }

    /**
     * @param Queue $queue
     * @param string $message
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     */
    protected function resetQueueingForProcessing(Queue $queue, $message, $entity)
    {
        // Check retry attempts and determine if we need to fail processing
        // Add a comment to the order indicating what has been done
        if ($queue->getAttempts() >= $this->avaTaxConfig->getQueueMaxRetryAttempts()) {
            $message .= __(' The processing has failed due to reaching the maximum number of attempts to retry. ' .
                'Any corrective measures will need to be initiated manually');

            // fail processing later by setting queue status to pending
            $this->failQueueProcessing($queue, $message);

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);
        } else {
            $message .= __(' The processing is set to automatically retry on the next processing attempt.');

            // retry processing later by setting queue status to pending
            $queue->setMessage($message);
            $queue->setQueueStatus(Queue::QUEUE_STATUS_PENDING);
            $queue->save();

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);
        }
    }

    /**
     * @param Queue $queue
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @param \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface $processSalesResponse
     */
    protected function completeQueueProcessing(
        Queue $queue,
        $entity,
        GetTaxResponseInterface $processSalesResponse
    ) {
        $message = __('%1 #%2 was submitted to AvaTax',
            ucfirst($queue->getEntityTypeCode()),
            $entity->getIncrementId()
        );
        $queueMessage = '';

        if ($processSalesResponse->getIsUnbalanced()) {
            $adjustmentMessage = null;
            if ($entity instanceof CreditmemoInterface) {
                if (abs($entity->getBaseAdjustmentNegative()) > 0 || abs($entity->getBaseAdjustmentPositive()) > 0) {
                    $adjustmentMessage = __('The difference was at least partly caused by the fact that the creditmemo '
                            . 'contained an adjustment of %1 and Magento doesn\'t factor that into its calculation, '
                            . 'but AvaTax does.',
                        $entity->getBaseAdjustment()
                    );
                }
            }

            $queueMessage = __('Unbalanced Response - Collected: %1, AvaTax Actual: %2',
                $entity->getBaseTaxAmount(),
                $processSalesResponse->getBaseAvataxTaxAmount()
            );
            if ($adjustmentMessage) {
                $queueMessage .= ' — ' . $adjustmentMessage;
            }

            // add comment about unbalanced amount
            $message .= '<br/>' .
                __('When submitting the %1 to AvaTax the amount calculated for tax differed from what was' .
                    ' recorded in Magento.', $queue->getEntityTypeCode()) . '<br/>' .
                __('There was a difference of %1',
                    ($entity->getBaseTaxAmount() - $processSalesResponse->getBaseAvataxTaxAmount())
                ) . '<br/>';

            if ($adjustmentMessage) {
                $message .= '<strong>' . $adjustmentMessage . '</strong><br/>';
            }

            $message .= __('Magento listed a tax amount of %1', $entity->getBaseTaxAmount()) . '<br/>' .
                __('AvaTax calculated the tax to be %1', $processSalesResponse->getBaseAvataxTaxAmount()) . '<br/>';
        }

        $queue->setMessage($queueMessage);
        $queue->setQueueStatus(Queue::QUEUE_STATUS_COMPLETE);
        $queue->save();

        // Add comment to order
        $this->addOrderComment($entity->getOrderId(), $message);
    }

    /**
     * @param int $orderId
     * @param string $message
     */
    protected function addOrderComment($orderId, $message)
    {
        /* @var $order \Magento\Sales\Api\Data\OrderInterface */
        $order = $this->orderRepository->get($orderId);

        // create comment
        $orderStatusHistory = $this->orderStatusHistoryFactory->create();
        $orderStatusHistory->setParentId($orderId);
        $orderStatusHistory->setComment($message);
        $orderStatusHistory->setIsCustomerNotified(false);
        $orderStatusHistory->setIsVisibleOnFront(false);
        $orderStatusHistory->setEntityName(Queue::ENTITY_TYPE_CODE_ORDER);
        $orderStatusHistory->setStatus($order->getStatus());

        // add comment to order
        $this->orderManagement->addComment($orderId, $orderStatusHistory);
    }
}
