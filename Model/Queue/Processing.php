<?php

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;
use Magento\Framework\Stdlib\DateTime;

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
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var Get\ResponseFactory
     */
    protected $getTaxResponseFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory
     */
    protected $orderStatusHistoryFactory;

    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Get $interactionGetTax,
        Get\ResponseFactory $getTaxResponseFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->interactionGetTax = $interactionGetTax;
        $this->getTaxResponseFactory = $getTaxResponseFactory;
        $this->dateTime = $dateTime;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
    }

    /**
     * Execute processing of the queued entity
     *
     * @param Queue $queue
     * @throws \Exception
     */
    public function execute(Queue $queue)
    {
        // validity check
        if ($queue->getQueueStatus() == Queue::QUEUE_STATUS_COMPLETE)
        {
            // We should not be attempting to process queue records that have already been marked as complete
            throw new \Exception('This record has already been processed, and the queue record marked as complete');
        }

        // update queue record with new processing status
        $queue->setQueueStatus(Queue::QUEUE_STATUS_PROCESSING);

        // update queue incrementing attempts
        $queue->setAttempts($queue->getAttempts()+1);

        /* @var $resource \ClassyLlama\AvaTax\Model\ResourceModel\Queue */
        $resource = $queue->getResource();
        $changedResult = $resource->changeQueueStatusWithLocking($queue);

        if (!$changedResult) {
            // Something else has modified the queue record, skip processing

            // This indicates something intercepted the queue record and changed it's status
            // before we were able to process it, like some other process was also attempting to
            // process queue records. We prefer not to send duplicates to AvaTax

            // log warning
            $this->avaTaxLogger->warning(
                'The queue status changed while attempting to process it. This could indicate multiple processes' .
                'attempting to process the same queue record at the same time.',
                [ /* context */
                    'queue_id' => $queue->getId(),
                    'entity_type_code' => $queue->getEntityTypeCode(),
                    'increment_id' => $queue->getIncrementId(),
                    'queue_status' => $queue->getQueueStatus(),
                    'updated_at' => $queue->getUpdatedAt()
                ]
            );

            throw new \Exception('Something else has modified the queue record, skip processing');
        }

        $entity = $this->getProcessingEntity($queue);

        // process entity with AvaTax
        /* @var $processSalesResponse \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response */
        $processSalesResponse = $this->getTaxResponseFactory->create();
        try {
            $processSalesResponse = $this->interactionGetTax->processSalesObject($entity);

            $message = ucfirst($queue->getEntityTypeCode()) . ' #' . $entity->getIncrementId() . ' was submitted to AvaTax';

            if ($processSalesResponse->getIsUnbalanced()) {
                $queue->setMessage(
                    'Unbalanced Response - Collected: ' . $entity->getBaseTaxAmount() . ', AvaTax Actual: ' . $processSalesResponse->getBaseAvataxTaxAmount() . "\n" .
                    $queue->getMessage()
                );

                // add comment about unbalanced amount
                $message .= '<br/>' .
                    'When submitting the ' . $queue->getEntityTypeCode() . ' to AvaTax the amount calculated for tax differed from what was recorded in Magento.<br/>' .
                    'There was a difference of ' . ($entity->getBaseTaxAmount() - $processSalesResponse->getBaseAvataxTaxAmount()) . '<br/>' .
                    'Magento listed a tax amount of ' . $entity->getBaseTaxAmount() . '<br/>' .
                    'AvaTax calculated the tax to be ' . $processSalesResponse->getBaseAvataxTaxAmount() . '<br/>';
            }

            // TODO: update invoice with additional fields
            $processSalesResponse->getIsUnbalanced();
            $processSalesResponse->getBaseAvataxTaxAmount();

            // Update the queue record status
            $queue->setQueueStatus(Queue::QUEUE_STATUS_COMPLETE);
            $queue->save();

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);
        } catch (Get\Exception $e) {
            // A problem happened while processing the sales object but we at least anticipated it's possibility

            $message = 'An error occurred when attempting ' .
                'to send ' . ucfirst($queue->getEntityTypeCode()) . ' #'. $entity->getIncrementId() . ' to AvaTax.';

            // TODO: Check to see if this is the last retry, and if so add an additional comment to the message

            // Update the queue record
            $queue->setMessage($message . "\n" . $queue->getMessage());
            $queue->setQueueStatus(Queue::QUEUE_STATUS_PENDING);
            $queue->save();

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);

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

            throw new \Exception($message, null, $e);
        } catch (\Exception $e) {

            $message = 'An unexpected exception occurred when attempting ' .
                'to send ' . ucfirst($queue->getEntityTypeCode()) . ' #'. $entity->getIncrementId() . ' to AvaTax.';

            // TODO: Check to see if this is the last retry, and if so add an additional comment to the message

            // Update the queue record
            $queue->setMessage($message . "\n" . $queue->getMessage());
            $queue->setQueueStatus(Queue::QUEUE_STATUS_PENDING);
            $queue->save();

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);

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

            throw new \Exception($message, null, $e);
        }
    }

    /**
     * Process invoice or credit memo
     * TODO: When failing a queue records we need to have some way to notify someone of the failure
     *
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
                    throw new \Exception('Invoice not found: (EntityId: ' . $queue->getEntityId() . ', IncrementId: ' . $queue->getIncrementId() . ')');
                }
            } catch (\Exception $e) {

                // Update the queue record
                $queue->setMessage('ERROR getProcessingEntity(): ' . $e->getMessage() . "\n" . $queue->getMessage());
                $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED);
                $queue->save();

                throw $e;
            }
        } elseif ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_CREDITMEMO) {

            try {

                /* @var $creditmemo \Magento\Sales\Api\Data\CreditmemoInterface */
                $creditmemo = $this->creditmemoRepository->get($queue->getEntityId());
                if ($creditmemo->getEntityId()) {
                    return $creditmemo;
                } else {
                    throw new \Exception('Credit Memo not found: (EntityId: ' . $queue->getEntityId() . ', IncrementId: ' . $queue->getIncrementId() . ')');
                }
            } catch (\Exception $e) {

                // Update the queue record
                $queue->setMessage('ERROR getProcessingEntity(): ' . $e->getMessage() . "\n" . $queue->getMessage());
                $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED);
                $queue->save();

                throw $e;
            }
        } else {
            $message = 'Unknown Entity Type Code for processing (' . $queue->getEntityTypeCode() . ')';
            // Update the queue record
            $queue->setMessage($message . "\n" . $queue->getMessage());
            $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED);
            $queue->save();

            throw new \Exception();
        }
    }

    /**
     * @param int $orderId
     * @param string $message
     */
    protected function addOrderComment($orderId, $message)
    {
        /* @var $order \Magento\Sales\Api\Data\OrderInterface */
        $order = $this->orderRepository->get($orderId);

        // TODO: remove extra debugging date time in comment
        $message .= "<br/>" . $this->dateTime->gmtDate() . " GMT";

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
