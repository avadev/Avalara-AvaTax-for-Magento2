<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\BaseProvider\Model;
 
use ClassyLlama\AvaTax\BaseProvider\Api\QueueRepositoryInterface;
use ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface;
use ClassyLlama\AvaTax\BaseProvider\Api\QueueSearchResultsInterface;
use ClassyLlama\AvaTax\BaseProvider\Api\QueueSearchResultsInterfaceFactory;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\Collection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueFactory
     */
    private $QueueFactory;
 
    /**
     * @var QueueCollectionFactory
     */
    private $QueueCollectionFactory;
 
    /**
     * @var QueueSearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
 
    public function __construct(
        QueueFactory $QueueFactory,
        QueueCollectionFactory $QueueCollectionFactory,
        QueueSearchResultsInterfaceFactory $QueueSearchResultsInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->QueueFactory = $QueueFactory;
        $this->QueueCollectionFactory = $QueueCollectionFactory;
        $this->searchResultFactory = $QueueSearchResultsInterfaceFactory;
        $this->collectionProcessor = collectionProcessor;
    }
 
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
       $collection = $this->collectionFactory->create();
       $this->collectionProcessor->process($searchCriteria, $collection);
       $searchResults = $this->searchResultFactory->create();
 
       $searchResults->setSearchCriteria($searchCriteria);
       $searchResults->setItems($collection->getItems());
 
       return $searchResults;
    } 
}