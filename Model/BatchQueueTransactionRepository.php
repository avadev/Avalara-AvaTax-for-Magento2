<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\BatchQueueTransactionRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionSearchResultsInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionSearchResultsInterfaceFactory;
use ClassyLlama\AvaTax\Exception\InvalidTypeException;
use ClassyLlama\AvaTax\Model\ResourceModel\BatchQueue\Collection;
use ClassyLlama\AvaTax\Model\ResourceModel\BatchQueue\CollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\BatchQueueTransactionFactory as ResourceBatchQueueTransactionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class BatchQueueTransactionRepository
 *
 * @package ClassyLlama\AvaTax\Model
 */
class BatchQueueTransactionRepository implements BatchQueueTransactionRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var BatchQueueTransactionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModel\BatchQueue\Collection
     */
    private $collection;

    /**
     * @var ResourceModel\BatchQueueTransaction
     */
    private $resourceBatchQueueTransaction;

    /**
     * @var ResourceModel\BatchQueueTransactionFactory
     */
    private $resourceBatchQueueTransactionFactory;

    /**
     * @var BatchQueueTransactionFactory
     */
    private $batchQueueTransactionFactory;

    /**
     * BatchQueueTransactionRepository constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Collection $collection
     * @param ResourceModel\BatchQueueTransaction $resourceBatchQueueTransaction
     * @param ResourceBatchQueueTransactionFactory $resourceBatchQueueTransactionFactory
     * @param BatchQueueTransactionFactory $batchQueueTransactionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param BatchQueueTransactionSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Collection $collection,
        \ClassyLlama\AvaTax\Model\ResourceModel\BatchQueueTransaction $resourceBatchQueueTransaction,
        ResourceBatchQueueTransactionFactory $resourceBatchQueueTransactionFactory,
        BatchQueueTransactionFactory $batchQueueTransactionFactory,
        CollectionProcessorInterface $collectionProcessor,
        BatchQueueTransactionSearchResultsInterfaceFactory $searchResultsFactory
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->collection = $collection;
        $this->resourceBatchQueueTransaction = $resourceBatchQueueTransaction;
        $this->resourceBatchQueueTransactionFactory = $resourceBatchQueueTransactionFactory;
        $this->batchQueueTransactionFactory = $batchQueueTransactionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param BatchQueueTransactionInterface $batchQueueTransaction
     * @return BatchQueueTransactionInterface
     * @throws InvalidTypeException|AlreadyExistsException
     */
    public function save(BatchQueueTransactionInterface $batchQueueTransaction): BatchQueueTransactionInterface
    {
        if (!($batchQueueTransaction instanceof BatchQueueTransaction)) {
            throw new InvalidTypeException(__('BatchQueueTransactionRepository implementation must be changed if BatchQueueTransactionInterface implementation changes'));
        }
        $classResource = $this->resourceBatchQueueTransactionFactory->create();
        $classResource->save($batchQueueTransaction);

        return $batchQueueTransaction;
    }

    /**
     * @param int $id
     * @return BatchQueueTransactionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): BatchQueueTransactionInterface
    {
        $batchQueueTransaction = $this->batchQueueTransactionFactory->create();
        $this->resourceBatchQueueTransaction->load($batchQueueTransaction, $id);
        if (!$batchQueueTransaction->getId()) {
            throw new NoSuchEntityException(__('Batch Queue Transaction with id "%1" does not exist.', $id));
        }

        return $batchQueueTransaction;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return BatchQueueTransactionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param BatchQueueTransactionInterface $batchQueueTransaction
     * @return bool
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     */
    public function delete(BatchQueueTransactionInterface $batchQueueTransaction): bool
    {
        if (!($batchQueueTransaction instanceof AbstractModel)) {
            throw new CouldNotSaveException(
                __(
                    'Could not delete the Batch Queue Transaction: %1',
                    'Batch Queue Transaction is not a model'
                )
            );
        }
        try {
            $this->resourceBatchQueueTransaction->delete($batchQueueTransaction);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the Batch Queue Transaction: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
