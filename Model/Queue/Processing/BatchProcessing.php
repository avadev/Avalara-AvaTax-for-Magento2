<?php

namespace ClassyLlama\AvaTax\Model\Queue\Processing;

use ClassyLlama\AvaTax\Api\BatchQueueTransactionRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use ClassyLlama\AvaTax\Model\InvoiceFactory;
use ClassyLlama\AvaTax\Model\CreditMemoFactory;

/**
 * Class BatchProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Queue\Processing
 */
class BatchProcessing extends AbstractProcessing
    implements ProcessingStrategyInterface
{
    const BATCH_COLLECTION_PAGE_SIZE = 1000;

    /**
     * @var RestTaxInterface
     */
    private $taxService;

    /**
     * @var Tax
     */
    private $interactionTax;

    /**
     * @var BatchQueueTransactionInterfaceFactory
     */
    private $batchQueueTransactionInterfaceFactory;

    /**
     * @var BatchQueueTransactionRepositoryInterface
     */
    private $batchQueueTransactionRepository;

    /**
     * @var CollectionFactory
     */
    private $queueCollectionFactory;

    /**
     * @var Collection
     */
    private $collection;


    /**
     * BatchProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param BatchQueueTransactionInterfaceFactory $batchQueueTransactionInterfaceFactory
     * @param BatchQueueTransactionRepositoryInterface $batchQueueTransactionRepository
     * @param Tax $interactionTax
     * @param CollectionFactory $collectionFactory
     * @param Collection $collection
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param InvoiceFactory $avataxInvoiceFactory
     * @param CreditMemoFactory $avataxCreditMemoFactory
     * @param RestTaxInterface $taxService
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        BatchQueueTransactionInterfaceFactory $batchQueueTransactionInterfaceFactory,
        BatchQueueTransactionRepositoryInterface $batchQueueTransactionRepository,
        Tax $interactionTax,
        CollectionFactory $collectionFactory,
        Collection $collection,
        ScopeConfigInterface $scopeConfig,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        InvoiceFactory $avataxInvoiceFactory,
        CreditMemoFactory $avataxCreditMemoFactory,
        RestTaxInterface $taxService
    ) {
        parent::__construct(
            $avaTaxLogger, $invoiceRepository, $creditmemoRepository, $scopeConfig,
            $orderStatusHistoryFactory, $orderRepository, $orderManagement, $avataxInvoiceFactory,
            $avataxCreditMemoFactory
        );
        $this->interactionTax = $interactionTax;
        $this->batchQueueTransactionInterfaceFactory = $batchQueueTransactionInterfaceFactory;
        $this->batchQueueTransactionRepository = $batchQueueTransactionRepository;
        $this->collection = $collection;
        $this->queueCollectionFactory = $collectionFactory;
        $this->taxService = $taxService;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        // Get Collection Last Page Number
        $queuePagesCountCollection = $this->queueCollectionFactory->create();
        $queuePagesCountCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PENDING)
            ->addCreatedAtBeforeFilter(self::QUEUE_PROCESSING_DELAY)
            ->setPageSize(self::BATCH_COLLECTION_PAGE_SIZE);
        $lastPageNumber = $queuePagesCountCollection->getLastPageNumber();

        if ($this->getLimit()) {
            $limitLastPage = ceil($this->getLimit() / self::BATCH_COLLECTION_PAGE_SIZE);
            $lastPageNumber = $lastPageNumber > $limitLastPage ? $limitLastPage : $lastPageNumber;
        }
        for ($page = 1; $page <= $lastPageNumber; $page++) {
            // Initialize the queue collection
            $queueCollection = $this->queueCollectionFactory->create();
            $queueCollection->addQueueStatusFilter(Queue::QUEUE_STATUS_PENDING)
                ->addCreatedAtBeforeFilter(self::QUEUE_PROCESSING_DELAY)
                ->setPageSize(self::BATCH_COLLECTION_PAGE_SIZE)
                ->setCurPage($page);
            $this->avaTaxLogger->debug("Queue Batch Collection Page $page processing");
            try {
                $batchQueueTransaction = $this->executeCollection($queueCollection);
                $this->processCount += $batchQueueTransaction->getRecordCount();
            } catch (Exception $e) {
            // Increment error count statistic
                $this->errorCount++;
                $previousException = $e->getPrevious();
                $errorMessage = $e->getMessage();
                if ($previousException instanceof Exception) {
                    $errorMessage .= " \nPREVIOUS ERROR: \n" . $previousException->getMessage();
                }
                $this->errorMessages[] = $errorMessage;
            }
        }
    }

    /**
     * @param Collection $collection
     * @return BatchQueueTransactionInterface
     * @throws LocalizedException
     * @throws ValidationException
     */
    public function executeCollection(Collection $collection): BatchQueueTransactionInterface
    {
        $this->avaTaxLogger->debug(__('Starting Batch Queue processing'));
        $startTime = microtime(true);
        $transactions = [];
        /** @var Queue $queue */
        foreach ($collection as $queue) {
            $this->initializeQueueProcessing($queue);
            $entity = $this->getProcessingEntity($queue);
            $getTaxRequest = $this->interactionTax->getTaxRequestForSalesObject($entity);
            $transactions[] = $getTaxRequest;
        }
        $result = $this->taxService->getTaxBatch($transactions);
        $resultData = $result->getData();
        $batchQueueTransaction = $this->batchQueueTransactionInterfaceFactory->create();

        $batchQueueTransaction->setBatchId($resultData["id"]);
        $batchQueueTransaction->setCompanyId($resultData[BatchQueueTransactionInterface::COMPANY_ID]);
        $batchQueueTransaction->setName($resultData[BatchQueueTransactionInterface::NAME]);
        $batchQueueTransaction->setStatus($resultData[BatchQueueTransactionInterface::STATUS]);
        $batchQueueTransaction->setRecordCount($resultData[BatchQueueTransactionInterface::RECORD_COUNT]);
        $batchQueueTransaction->setInputFileId(array_shift($resultData["files"])["id"]);
        $this->batchQueueTransactionRepository->save($batchQueueTransaction);
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        $count = $resultData[BatchQueueTransactionInterface::RECORD_COUNT];
        $this->avaTaxLogger->debug("Collection with $count items processed at $time seconds");

        return $batchQueueTransaction;
    }
}
