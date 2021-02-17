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

namespace ClassyLlama\AvaTax\Model\Queue\Processing;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;
use ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface;
use ClassyLlama\AvaTax\Model\InvoiceFactory;
use ClassyLlama\AvaTax\Model\CreditMemoFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use Exception;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Queue Processing
 */
class NormalProcessing extends AbstractProcessing implements ProcessingStrategyInterface
{
    /**
     * @var Get
     */
    private $interactionGetTax;

    /**
     * @var Config
     */
    private $avaTaxConfig;

    /**
     * @var CollectionFactory
     */
    private $queueCollectionFactory;

    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ScopeConfigInterface $scopeConfig,
        Get $interactionGetTax,
        InvoiceFactory $avataxInvoiceFactory,
        CreditMemoFactory $avataxCreditMemoFactory,
        Config $avaTaxConfig,
        CollectionFactory $collectionFactory,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct(
            $avaTaxLogger, $invoiceRepository, $creditmemoRepository, $scopeConfig,
            $orderStatusHistoryFactory, $orderRepository, $orderManagement, $avataxInvoiceFactory,
            $avataxCreditMemoFactory
        );
        $this->interactionGetTax = $interactionGetTax;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueCollectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $this->avaTaxLogger->debug(__('Starting Normal Queue processing'));
        // Initialize the queue collection
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PENDING)
            ->addCreatedAtBeforeFilter(self::QUEUE_PROCESSING_DELAY);

        // Process each queued entity
        /** @var $queue Queue */
        foreach ($queueCollection as $queue) {
            // Process queue
            try {
                $this->processQueueEntry($queue);
                // Increment process count statistic
                $this->processCount++;
            } catch (Exception $e) {

                // Increment error count statistic
                $this->errorCount++;
                $previousException = $e->getPrevious();
                $errorMessage = $e->getMessage();
                if ($previousException instanceof Exception) {
                    $errorMessage .= " \nPREVIOUS ERROR: \n" . $previousException->getMessage();
                }
                // If record has been sent to AvaTax, immediately mark as failure
                // to prevent duplicate records from being sent. This situation is likely to occur
                // when associated record (invoice or credit memo) is unable to be saved.
                if ($queue->getHasRecordBeenSentToAvaTax()) {
                    $errorMessage = 'Record was sent to AvaTax, but error occurred after sending record: '
                        . $errorMessage;
                    // update queue record with new processing status
                    $queue->setQueueStatus(Queue::QUEUE_STATUS_FAILED)
                        ->setMessage($errorMessage)
                        ->save();
                }
                $this->errorMessages[] = $errorMessage;
            }
        }
    }

    /**
     * Execute processing of the queued entity
     *
     * @param Queue $queue
     * @throws Exception
     */
    public function processQueueEntry(Queue $queue)
    {
        // Initialize the queue processing
        // Check for valid queue status that allows processing
        // Update queue status and attempts on this record
        $this->initializeQueueProcessing($queue);

        // Get the credit memo or invoice entity
        $entity = $this->getProcessingEntity($queue);

        // Process entity with AvaTax
        $processSalesResponse = $this->processWithAvaTax($queue, $entity);

        // Create AvaTax record
        $this->saveAvaTaxRecord($entity, $processSalesResponse);

        // Update the queue record status
        // and add comment to order
        $this->completeQueueProcessing($queue, $entity, $processSalesResponse);
    }

    /**
     * @param Queue $queue
     * @param InvoiceInterface|CreditmemoInterface $entity
     * @return GetTaxResponseInterface
     * @throws Exception
     */
    protected function processWithAvaTax(Queue $queue, $entity): GetTaxResponseInterface
    {
        try {
            $processSalesResponse = $this->interactionGetTax->processSalesObject($entity);
            $queue->setHasRecordBeenSentToAvaTax(true);
        } catch (Exception $e) {

            $message = __('An error occurred when attempting to send %1 #%2 to AvaTax. Error: %3',
                ucfirst($queue->getEntityTypeCode()),
                $entity->getIncrementId(),
                $e->getMessage()
            );

            // Log the error
            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                  'queue_id'         => $queue->getId(),
                  'entity_type_code' => $queue->getEntityTypeCode(),
                  'increment_id'     => $queue->getIncrementId(),
                  'exception'        => sprintf(
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

            throw new Exception($message, null, $e);
        }

        return $processSalesResponse;
    }

    /**
     * @param Queue $queue
     * @param string $message
     * @param InvoiceInterface|CreditmemoInterface $entity
     * @throws Exception
     */
    protected function resetQueueingForProcessing(Queue $queue, string $message, $entity)
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
}
