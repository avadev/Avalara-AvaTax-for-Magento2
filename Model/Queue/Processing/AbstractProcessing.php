<?php

namespace ClassyLlama\AvaTax\Model\Queue\Processing;

use ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface;
use ClassyLlama\AvaTax\Model\CreditMemo;
use ClassyLlama\AvaTax\Model\Invoice;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\ResourceModel\CreditMemo as CreditMemoResourceModel;
use ClassyLlama\AvaTax\Model\ResourceModel\Invoice as InvoiceResourceModel;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ClassyLlama\AvaTax\Model\InvoiceFactory;
use ClassyLlama\AvaTax\Model\CreditMemoFactory;

/**
 * Class AbstractProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Queue\Processing
 */
abstract class AbstractProcessing implements ProcessingStrategyInterface
{

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var int|bool
     */
    protected $limit = false;

    /**
     * @var int
     */
    protected $processCount = 0;

    /**
     * @var int
     */
    protected $errorCount = 0;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var int
     */
    protected $resetCount = 0;

    /**
     * @var int
     */
    protected $deleteCompleteCount = 0;

    /**
     * @var int
     */
    protected $deleteFailedCount = 0;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $orderStatusHistoryFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var InvoiceFactory
     */
    private $avataxInvoiceFactory;

    /**
     * @var CreditMemoFactory
     */
    private $avataxCreditMemoFactory;


    /**
     * AbstractProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param InvoiceFactory $avataxInvoiceFactory
     * @param CreditMemoFactory $avataxCreditMemoFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ScopeConfigInterface $scopeConfig,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        InvoiceFactory $avataxInvoiceFactory,
        CreditMemoFactory $avataxCreditMemoFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->scopeConfig = $scopeConfig;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->avataxInvoiceFactory = $avataxInvoiceFactory;
        $this->avataxCreditMemoFactory = $avataxCreditMemoFactory;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getProcessCount(): int
    {
        return $this->processCount;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @param Queue $queue
     * @throws Exception
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
                  'queue_id'         => $queue->getId(),
                  'entity_type_code' => $queue->getEntityTypeCode(),
                  'increment_id'     => $queue->getIncrementId(),
                  'queue_status'     => $queue->getQueueStatus(),
                  'updated_at'       => $queue->getUpdatedAt()
                ]
            );

            throw new Exception(__('The queue record has already been processed, and the queue record marked as complete'));
        }

        // update queue record with new processing status
        $queue->setQueueStatus(Queue::QUEUE_STATUS_PROCESSING);

        // update queue incrementing attempts
        $queue->setAttempts($queue->getAttempts() + 1);

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
                  'queue_id'         => $queue->getId(),
                  'entity_type_code' => $queue->getEntityTypeCode(),
                  'increment_id'     => $queue->getIncrementId(),
                  'queue_status'     => $queue->getQueueStatus(),
                  'updated_at'       => $queue->getUpdatedAt()
                ]
            );

            throw new Exception(__('Something else has modified the queue record, skip processing'));
        }
    }

    /**
     * Process invoice or credit memo
     *
     * @param Queue $queue
     * @return InvoiceInterface|CreditmemoInterface
     * @throws Exception
     */
    protected function getProcessingEntity(Queue $queue)
    {
        // Check to see which type of entity we are processing
        if ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_INVOICE) {

            try {
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

                    throw new Exception($message);
                }
            } catch (NoSuchEntityException $e) {
                $message = __('Queue ID: %1 - Invoice not found: (EntityId: %2, IncrementId: %3)',
                    $queue->getId(),
                    $queue->getEntityId(),
                    $queue->getIncrementId()
                );

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw new NoSuchEntityException($message, $e);
            } catch (Exception $e) {
                $message = __('Unexpected Exception getProcessingEntity() invoiceRepository->get(): ') . "\n" .
                    $e->getMessage() . "\n" .
                    $queue->getMessage();

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw new Exception($message);
            }
        } elseif ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_CREDITMEMO) {

            try {

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

                    throw new Exception($message);
                }
            } catch (Exception $e) {
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

            throw new Exception();
        }
    }

    /**
     * Set queue to failed
     *
     * @param Queue $queue
     * @param string $message
     * @throws Exception
     */
    protected function failQueueProcessing(Queue $queue, string $message)
    {
        $queue->setMessage($message);
        $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED);
        $queue->save();
    }

    /**
     * @param InvoiceInterface|CreditmemoInterface $entity
     * @param GetTaxResponseInterface $processSalesResponse
     * @throws Exception
     */
    public function saveAvaTaxRecord(
        $entity,
        GetTaxResponseInterface $processSalesResponse
    ) {
        // Get the associated AvataxEntity record (related to extension attributes) for this entity type
        $avaTaxRecord = $this->getAvataxEntity($entity);

        if ($entity->getExtensionAttributes()) {
            $avaTaxRecord->setAvataxResponse($entity->getExtensionAttributes()->getAvataxResponse());
        }

        if ($avaTaxRecord->getParentId()) {
            // Record exists, compare existing values to new

            // Check to see if isUnbalanced is already set on this entity
            $avataxIsUnbalancedToSave = false;
            if ($avaTaxRecord->getIsUnbalanced() == null) {
                $avaTaxRecord->setIsUnbalanced($processSalesResponse->getIsUnbalanced());
                $avataxIsUnbalancedToSave = true;
            } else {
                // check to see if any existing value is different from the new value
                if ($processSalesResponse->getIsUnbalanced() <> $avaTaxRecord->getIsUnbalanced()) {
                    // Log the warning
                    $this->avaTaxLogger->warning(
                        __('When processing an entity in the queue there was an existing AvataxIsUnbalanced and ' .
                            'the new value was different than the old one. The old value was overwritten.'),
                        [ /* context */
                          'old_is_unbalanced' => $avaTaxRecord->getIsUnbalanced(),
                          'new_is_unbalanced' => $processSalesResponse->getIsUnbalanced(),
                        ]
                    );
                    $avaTaxRecord->setIsUnbalanced($processSalesResponse->getIsUnbalanced());
                    $avataxIsUnbalancedToSave = true;
                }
            }

            // Check to see if the BaseAvataxTaxAmount is already set on this entity
            $baseAvataxTaxAmountToSave = false;
            if ($avaTaxRecord->getBaseAvataxTaxAmount() == null) {
                $avaTaxRecord->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
                $baseAvataxTaxAmountToSave = true;
            } else {
                // Check to see if any existing value is different from the new value
                if ($processSalesResponse->getBaseAvataxTaxAmount() <> $avaTaxRecord->getBaseAvataxTaxAmount()) {
                    // Log the warning
                    $this->avaTaxLogger->warning(
                        __('When processing an entity in the queue there was an existing BaseAvataxTaxAmount and ' .
                            'the new value was different than the old one. The old value was overwritten.'),
                        [ /* context */
                          'old_base_avatax_tax_amount' => $avaTaxRecord->getBaseAvataxTaxAmount(),
                          'new_base_avatax_tax_amount' => $processSalesResponse->getBaseAvataxTaxAmount(),
                        ]
                    );
                    $avaTaxRecord->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
                    $baseAvataxTaxAmountToSave = true;
                }
            }
        } else {
            // No entry exists for entity ID, add data to entry and set store flags to true
            $avataxIsUnbalancedToSave = true;
            $baseAvataxTaxAmountToSave = true;
            $avaTaxRecord->setParentId($entity->getId());
            $avaTaxRecord->setIsUnbalanced($processSalesResponse->getIsUnbalanced());
            $avaTaxRecord->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
        }

        if ($avataxIsUnbalancedToSave || $baseAvataxTaxAmountToSave) {
            // Save the AvaTax entry
            $avaTaxRecord->save();
        }
    }

    /**
     * @param InvoiceInterface|CreditmemoInterface $entity
     * @return CreditMemo|Invoice
     * @throws Exception
     */
    protected function getAvataxEntity($entity)
    {
        if ($entity instanceof InvoiceInterface) {
            /** @var Invoice $avaTaxRecord */
            $avaTaxRecord = $this->avataxInvoiceFactory->create();

            // Load existing AvaTax entry for this entity, if exists
            $avaTaxRecord->load($entity->getId(), InvoiceResourceModel::PARENT_ID_FIELD_NAME);

            return $avaTaxRecord;
        } elseif ($entity instanceof CreditmemoInterface) {
            /** @var CreditMemo $avaTaxRecord */
            $avaTaxRecord = $this->avataxCreditMemoFactory->create();

            // Load existing AvaTax entry for this entity, if exists
            $avaTaxRecord->load($entity->getId(), CreditMemoResourceModel::PARENT_ID_FIELD_NAME);

            return $avaTaxRecord;
        } else {
            $message = __('Did not receive a valid entity instance to determine the factory type to return');
            throw new Exception($message);
        }
    }

    /**
     * @param Queue $queue
     * @param InvoiceInterface|CreditmemoInterface $entity
     * @param GetTaxResponseInterface $processSalesResponse
     * @throws Exception
     */
    public function completeQueueProcessing(
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
                    if ((bool)$this->scopeConfig->getValue(
                        AvataxAdjustmentTaxes::ADJUSTMENTS_CONFIG_PATH,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        $entity->getStoreId()
                    )
                    ) {
                        $adjustmentMessage = __('The difference was caused by the different tax calculation '
                            . 'on the Magento side and Avalara.');
                    } else {
                        $adjustmentMessage
                            = __('The difference was at least partly caused by the fact that the creditmemo '
                            . 'contained an adjustment of %1 and Magento doesn\'t factor that into its calculation, '
                            . 'but AvaTax does.',
                            $entity->getBaseAdjustment()
                        );
                    }
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
    protected function addOrderComment(int $orderId, string $message)
    {
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
