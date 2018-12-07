<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Tax;

use ClassyLlama\AvaTax\Api\AssociatedTaxableRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterface;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterfaceFactory as AssociatedTaxableFactory;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableSearchResultsInterface;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableSearchResultsInterfaceFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable\Collection;
use ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class AssociatedTaxableRepository implements AssociatedTaxableRepositoryInterface
{
    protected $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable
     */
    protected $resource;

    /**
     * @var AssociatedTaxableFactory
     */
    protected $associatedTaxableModelFactory;

    /**
     * @var AssociatedTaxableSearchResultsInterfaceFactory
     */
    protected $associatedTaxableSearchResultsFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilderFactory;

    public function __construct(
        \ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxableFactory $resourceFactory,
        AssociatedTaxableFactory $associatedTaxableModelFactory,
        AssociatedTaxableSearchResultsInterfaceFactory $associatedTaxableSearchResultsInterfaceFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->resource = $resourceFactory->create();
        $this->associatedTaxableModelFactory = $associatedTaxableModelFactory;
        $this->associatedTaxableSearchResultsFactory = $associatedTaxableSearchResultsInterfaceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param integer $associatedTaxableId
     *
     * @return \ClassyLlama\AvaTax\Model\ResourceModel\Tax\AssociatedTaxable
     */
    public function getById($associatedTaxableId)
    {
        $model = $this->associatedTaxableModelFactory->create();
        $entity = $this->resource->load($model, $associatedTaxableId);
        return $entity;
    }

    /**
     * @param AssociatedTaxableInterface $associatedTaxable
     *
     * @return AssociatedTaxableInterface
     * @throws AlreadyExistsException
     * @throws \Exception
     */
    public function save(AssociatedTaxableInterface $associatedTaxable)
    {
        $this->resource->save($associatedTaxable);
        return $associatedTaxable;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return AssociatedTaxableSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var AssociatedTaxableSearchResultsInterface $searchResults */
        $searchResults = $this->associatedTaxableSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $associatedTaxableId
     *
     * @return bool true on success
     */
    public function deleteById($associatedTaxableId)
    {
        $entity = $this->getById($associatedTaxableId);

        try {
            $this->resource->delete($entity);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param integer $orderId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getQuoteAssociatedTaxablesForOrder($orderId)
    {
        $this->filterBuilder->setField(AssociatedTaxableInterface::ORDER_ID);
        $this->filterBuilder->setValue($orderId);
        /** @var \Magento\Framework\Api\Filter $orderIdFilter */
        $orderIdFilter = $this->filterBuilder->create();

        $this->filterBuilder->setField(AssociatedTaxableInterface::ASSOCIATED_ITEM_CODE);
        $this->filterBuilder->setValue(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE);
        /** @var \Magento\Framework\Api\Filter $quoteFilter */
        $quoteFilter = $this->filterBuilder->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter($quoteFilter);
        $searchCriteriaBuilder->addFilter($orderIdFilter);

        /** @var \Magento\Framework\Api\Search\SearchCriteria $criteria */
        $criteria = $searchCriteriaBuilder->create();

        return $this->getList($criteria)->getItems();
    }

    /**
     * @param integer $orderId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getItemAssociatedTaxablesForOrder($orderId)
    {
        $this->filterBuilder->setField(AssociatedTaxableInterface::ORDER_ID);
        $this->filterBuilder->setValue($orderId);
        /** @var \Magento\Framework\Api\Filter $orderIdFilter */
        $orderIdFilter = $this->filterBuilder->create();

        $this->filterBuilder->setField(AssociatedTaxableInterface::ASSOCIATED_ITEM_CODE);
        $this->filterBuilder->setValue(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE);
        $this->filterBuilder->setConditionType('neq');
        /** @var \Magento\Framework\Api\Filter $quoteNotItemFilter */
        $quoteNotItemFilter = $this->filterBuilder->create();

        $this->filterBuilder->setField(AssociatedTaxableInterface::ASSOCIATED_ITEM_CODE);
        $this->filterBuilder->setValue(null);
        $this->filterBuilder->setConditionType('null');

        /** @var \Magento\Framework\Api\Filter $quoteIsNullFilter */
        $quoteIsNullFilter = $this->filterBuilder->create();

        $this->filterGroupBuilder->addFilter($quoteNotItemFilter);
        $this->filterGroupBuilder->addFilter($quoteIsNullFilter);
        // filter group for item code NOT NULL or != 'quote'
        $filterGroup = $this->filterGroupBuilder->create();
        $this->filterGroupBuilder->addFilter($orderIdFilter);

        $filterGroup2 = $this->filterGroupBuilder->create();
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        /** @var \Magento\Framework\Api\Search\SearchCriteria $criteria */
        $criteria = $searchCriteriaBuilder->create();
        $criteria->setFilterGroups([$filterGroup, $filterGroup2]);

        return $this->getList($criteria)->getItems();
    }

    /**
     * @param integer $invoiceId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getAllAssociatedTaxablesForInvoice($invoiceId)
    {
        $this->filterBuilder->setField(AssociatedTaxableInterface::INVOICE_ID);
        $this->filterBuilder->setValue($invoiceId);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        return $this->getList($searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param int $creditMemoId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getAllAssociatedTaxablesForCreditMemo($creditMemoId)
    {
        $this->filterBuilder->setField(AssociatedTaxableInterface::CREDIT_MEMO_ID);
        $this->filterBuilder->setValue($creditMemoId);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        return $this->getList($searchCriteriaBuilder->create())->getItems();
    }
}
