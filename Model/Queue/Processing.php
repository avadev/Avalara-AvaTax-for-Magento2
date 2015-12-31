<?php

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;
use Magento\Framework\Stdlib\DateTime;
use ClassyLlama\AvaTax\Model\Config;

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

    /**
     * @var \Magento\Sales\Api\Data\InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        Get $interactionGetTax,
        Get\ResponseFactory $getTaxResponseFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        \Magento\Sales\Api\Data\InvoiceExtensionFactory $invoiceExtensionFactory,
        \Magento\Sales\Api\Data\CreditmemoExtensionFactory $creditmemoExtensionFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->interactionGetTax = $interactionGetTax;
        $this->getTaxResponseFactory = $getTaxResponseFactory;
        $this->dateTime = $dateTime;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
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
        if ($queue->getQueueStatus() == Queue::QUEUE_STATUS_COMPLETE)
        {
            // We should not be attempting to process queue records that have already been marked as complete

            // log warning
            $this->avaTaxLogger->warning(
                'Processing was attempted on a queue record that has already been processed and marked as completed.',
                [ /* context */
                    'queue_id' => $queue->getId(),
                    'entity_type_code' => $queue->getEntityTypeCode(),
                    'increment_id' => $queue->getIncrementId(),
                    'queue_status' => $queue->getQueueStatus(),
                    'updated_at' => $queue->getUpdatedAt()
                ]
            );

            throw new \Exception('The queue record has already been processed, and the queue record marked as complete');
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
                    $message = 'Invoice not found: (EntityId: ' . $queue->getEntityId() . ', IncrementId: ' . $queue->getIncrementId() . ')';

                    // Update the queue record
                    $this->failQueueProcessing($queue, $message);

                    throw new \Exception($message);
                }
            } catch (\Exception $e) {
                $message = 'ERROR getProcessingEntity() invoiceRepository->get(): ' . $e->getMessage() . "\n" . $queue->getMessage();

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw $e;
            }
        } elseif ($queue->getEntityTypeCode() === Queue::ENTITY_TYPE_CODE_CREDITMEMO) {

            try {

                /* @var $creditmemo \Magento\Sales\Api\Data\CreditmemoInterface */
                $creditmemo = $this->creditmemoRepository->get($queue->getEntityId());
                if ($creditmemo->getEntityId()) {
                    return $creditmemo;
                } else {
                    $message = 'Credit Memo not found: (EntityId: ' . $queue->getEntityId() . ', IncrementId: ' . $queue->getIncrementId() . ')';

                    // Update the queue record
                    $this->failQueueProcessing($queue, $message);

                    throw new \Exception($message);
                }
            } catch (\Exception $e) {
                $message = 'ERROR getProcessingEntity() creditmemoRepository->get(): ' . $e->getMessage() . "\n" . $queue->getMessage();

                // Update the queue record
                $this->failQueueProcessing($queue, $message);

                throw $e;
            }
        } else {
            $message = 'Unknown Entity Type Code for processing (' . $queue->getEntityTypeCode() . ')';

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
            if ($e instanceof Get\Exception)
            {
                $message .= 'An error occurred when attempting ' .
                    'to send ' . ucfirst($queue->getEntityTypeCode()) . ' #'. $entity->getIncrementId() . ' to AvaTax.';
            } else {
                $message .= 'An unexpected exception occurred when attempting ' .
                    'to send ' . ucfirst($queue->getEntityTypeCode()) . ' #'. $entity->getIncrementId() . ' to AvaTax.';
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
    protected function updateAdditionalEntityAttributes($entity, \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface $processSalesResponse)
    {
        // TODO: update invoice with additional fields
        $entityExtension = $entity->getExtensionAttributes();
        if ($entityExtension == null) {
            $entityExtension = $this->getEntityExtensionInterface($entity);
        }

        $entityExtension->setAvataxIsUnbalanced($processSalesResponse->getIsUnbalanced());
        $entityExtension->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
        $entity->setExtensionAttributes($entityExtension);
        $entityRepository = $this->getEntityRepository($entity);
        $entityRepository->save($entity);

        //$entity->setAvataxIsUnbalanced($processSalesResponse->getIsUnbalanced());
        //$entity->setBaseAvataxTaxAmount($processSalesResponse->getBaseAvataxTaxAmount());
        //$entity->save();
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Sales\Api\Data\InvoiceExtension|\Magento\Sales\Api\Data\CreditmemoExtension
     * @throws \Exception
     */
    protected function getEntityExtensionInterface($entity)
    {
        if ($entity instanceof \Magento\Sales\Api\Data\InvoiceInterface)
        {
            return $this->invoiceExtensionFactory->create();
        } elseif ($entity instanceof \Magento\Sales\Api\Data\CreditmemoInterface) {
            return $this->creditmemoExtensionFactory->create();
        } else {
            $message = 'Did not receive a valid entity instance to determine the extension to return';
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
        if ($entity instanceof \Magento\Sales\Api\Data\InvoiceInterface)
        {
            return $this->invoiceRepository;
        } elseif ($entity instanceof \Magento\Sales\Api\Data\CreditmemoInterface) {
            return $this->creditmemoRepository;
        } else {
            $message = 'Did not receive a valid entity instance to determine the repository type to return';
            throw new \Exception($message);
        }
    }

    /**
     * TODO: When failing a queue records we need to have some way to notify someone of the failure (like an email?)
     *
     * @param Queue $queue
     * @param string $message
     */
    protected function failQueueProcessing(Queue $queue, $message)
    {
        $queue->setMessage($message . "\n" . $queue->getMessage());
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
        if ($this->avaTaxConfig->getQueueMaxRetryAttempts() >= $queue->getAttempts())
        {
            $message .= ' The processing has failed due to reaching the maximum number of attempts to retry. ' .
                'Any corrective measures will need to be initiated manually';

            // fail processing later by setting queue status to pending
            $this->failQueueProcessing($queue, $message);

            // Add comment to order
            $this->addOrderComment($entity->getOrderId(), $message);
        } else {
            $message .= ' The processing is set to automatically retry on the next processing attempt.';

            // retry processing later by setting queue status to pending
            $queue->setMessage($message . "\n" . $queue->getMessage());
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
        \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface $processSalesResponse
    ) {
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
