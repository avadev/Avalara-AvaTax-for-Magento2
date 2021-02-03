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

namespace ClassyLlama\AvaTax\Model\Queue;

use ClassyLlama\AvaTax\Api\BatchQueueTransactionRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use ClassyLlama\AvaTax\Api\QueueRepositoryInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\ResponseFactory;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\Queue\Processing\BatchProcessing;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class BatchResponseProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Queue
 */
class BatchResponseProcessing
{
    const INVOICE_TYPE = "SalesInvoice";
    const CREDITMEMO_TYPE = "ReturnOrder";

    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * @var BatchQueueTransactionRepositoryInterface
     */
    private $batchQueueTransactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ClientPool
     */
    private $clientPool;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var InvoiceInterface
     */
    private $invoice;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var Processing
     */
    private $processing;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * BatchResponseProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param BatchQueueTransactionRepositoryInterface $batchQueueTransactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ClientPool $clientPool
     * @param ResponseFactory $responseFactory
     * @param InvoiceInterface $invoice
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param BatchProcessing $processing
     * @param QueueRepositoryInterface $queueRepository
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        BatchQueueTransactionRepositoryInterface $batchQueueTransactionRepository,

        SearchCriteriaBuilder $searchCriteriaBuilder,
        ClientPool $clientPool,
        ResponseFactory $responseFactory,
        InvoiceInterface $invoice,
        CreditmemoRepositoryInterface $creditmemoRepository,
        BatchProcessing $processing,
        QueueRepositoryInterface $queueRepository
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->batchQueueTransactionRepository = $batchQueueTransactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->clientPool = $clientPool;
        $this->responseFactory = $responseFactory;
        $this->invoice = $invoice;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->processing = $processing;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @throws LocalizedException
     */
    public function cronProcessResponse()
    {
        $this->avaTaxLogger->debug(__('Starting Batch Queue Response Processing'));
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(BatchQueueTransactionInterface::STATUS,
            BatchQueueTransactionInterface::WAITING_STATUS)
            ->create();
        $collection = $this->batchQueueTransactionRepository->getList($searchCriteria);
        $client = $this->clientPool->getClient();
        /** @var BatchQueueTransactionInterface $batchQueueTransaction */
        foreach ($collection->getItems() as $batchQueueTransaction) {
            $startTime = microtime(true);
            $batch = $client->getClient()
                ->getBatch($batchQueueTransaction->getCompanyId(), $batchQueueTransaction->getBatchId());
            $this->avaTaxLogger->debug("Get Batch", json_decode(json_encode($batch), true));
            if ($batch->{BatchQueueTransactionInterface::STATUS} == BatchQueueTransactionInterface::COMPLETED_STATUS) {
                $resultFile = array_pop($batch->files);
                if ($resultFile->name == "Result") {
                    $resultBatchFile = $client->getClient()
                        ->downloadBatch($batchQueueTransaction->getCompanyId(), $resultFile->batchId, $resultFile->id);
                    foreach ($resultBatchFile->results as $item) {
                        $avataxTaxAmount = abs($item->transactionResult->totalTax);
                        if ($item->transactionResult->type == self::INVOICE_TYPE) {
                            $object = $this->invoice->loadByIncrementId($item->transactionResult->purchaseOrderNo);
                            $queueEntityType = 'invoice';
                        } else {
                            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id',
                                $item->transactionResult->purchaseOrderNo)->create();
                            $creditMemos = $this->creditmemoRepository->getList($searchCriteria)->getItems();
                            $object = array_shift($creditMemos);
                            $queueEntityType = 'creditmemo';
                        }
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('increment_id', $item->transactionResult->purchaseOrderNo)
                            ->addFilter('entity_type_code', $queueEntityType)
                            ->create();
                        $queueEntity = $this->queueRepository->getList($searchCriteria)->getItems();
                        $queueEntity = array_shift($queueEntity);

                        $unbalanced = ($avataxTaxAmount != $object->getBaseTaxAmount());
                        $responseObj = $this->responseFactory->create();
                        $responseObj->setIsUnbalanced($unbalanced)->setBaseAvataxTaxAmount($avataxTaxAmount);
                        $this->processing->saveAvaTaxRecord($object, $responseObj);
                        $this->processing->completeQueueProcessing($queueEntity, $object, $responseObj);
                    }
                    $batchQueueTransaction->setStatus(BatchQueueTransactionInterface::COMPLETED_STATUS);
                    $this->batchQueueTransactionRepository->save($batchQueueTransaction);

                    $batchId = $batchQueueTransaction->getBatchId();
                    $batchName = $batchQueueTransaction->getName();
                    $endTime = microtime(true);
                    $time = $endTime - $startTime;
                    $this->avaTaxLogger->debug("Processed Queue Transaction Batch Response #$batchId, Batch Name $batchName. Takes $time sec.");
                }
            }
        }
    }
}
