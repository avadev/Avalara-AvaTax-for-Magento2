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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\CrossBorderTypeRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterfaceFactory;
use ClassyLlama\AvaTax\Api\Data\CrossBorderTypeSearchResultsInterfaceFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderType as ResourceCrossBorderType;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderType\CollectionFactory as CrossBorderTypeCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class CrossBorderTypeRepository implements CrossBorderTypeRepositoryInterface
{
    /**
     * @var CrossBorderTypeInterfaceFactory
     */
    protected $dataCrossBorderTypeFactory;

    /**
     * @var CrossBorderTypeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CrossBorderTypeFactory
     */
    protected $crossBorderTypeFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ResourceCrossBorderType
     */
    protected $resource;

    /**
     * @var CrossBorderTypeCollectionFactory
     */
    protected $crossBorderTypeCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ResourceCrossBorderType                      $resource
     * @param CrossBorderTypeFactory                       $crossBorderTypeFactory
     * @param CrossBorderTypeInterfaceFactory              $dataCrossBorderTypeFactory
     * @param CrossBorderTypeCollectionFactory             $crossBorderTypeCollectionFactory
     * @param CrossBorderTypeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper                             $dataObjectHelper
     * @param DataObjectProcessor                          $dataObjectProcessor
     * @param StoreManagerInterface                        $storeManager
     */
    public function __construct(
        ResourceCrossBorderType $resource,
        CrossBorderTypeFactory $crossBorderTypeFactory,
        CrossBorderTypeInterfaceFactory $dataCrossBorderTypeFactory,
        CrossBorderTypeCollectionFactory $crossBorderTypeCollectionFactory,
        CrossBorderTypeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    )
    {
        $this->resource = $resource;
        $this->crossBorderTypeFactory = $crossBorderTypeFactory;
        $this->crossBorderTypeCollectionFactory = $crossBorderTypeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCrossBorderTypeFactory = $dataCrossBorderTypeFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType)
    {
        if(!($crossBorderType instanceof AbstractModel)) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the Cross Border Type: %1',
                    'cross border type is not a model'
                )
            );
        }

        try {
            $this->resource->save($crossBorderType);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the Cross Border Type: %1',
                    $exception->getMessage()
                )
            );
        }

        return $crossBorderType;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $crossBorderType = $this->crossBorderTypeFactory->create();
        $this->resource->load($crossBorderType, $id);
        if (!$crossBorderType->getId()) {
            throw new NoSuchEntityException(__('Cross Border Type with id "%1" does not exist.', $id));
        }

        return $crossBorderType;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->crossBorderTypeCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType)
    {
        if(!($crossBorderType instanceof AbstractModel)) {
            throw new CouldNotSaveException(
                __(
                    'Could not delete the Cross Border Type: %1',
                    'cross border type is not a model'
                )
            );
        }

        try {
            $this->resource->delete($crossBorderType);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the Cross Border Type: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
