<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\Data\QueueSearchResultsInterfaceFactory;
use ClassyLlama\AvaTax\Api\QueueRepositoryInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class QueueRepository
 *
 * @package ClassyLlama\AvaTax\Model
 */
class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var QueueSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;


    /**
     * QueueRepository constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param QueueSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        QueueSearchResultsInterfaceFactory $searchResultsFactory
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }


    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
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

}
