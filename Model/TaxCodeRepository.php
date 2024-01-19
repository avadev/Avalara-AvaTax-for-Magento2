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

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\TaxCodeRepositoryInterface;
use ClassyLlama\AvaTax\Model\TaxCodeSyncFactory;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Tax Code repository.
 *
 * CRUD operations for tax class code
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class TaxCodeRepository implements TaxCodeRepositoryInterface
{
    /**
     * @var TaxCodeSyncFactory
     */
    protected $taxCodeSyncFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param TaxCodeSyncFactory $taxCodeSyncFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        TaxCodeSyncFactory $taxCodeSyncFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->taxCodeSyncFactory = $taxCodeSyncFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Retrieve tax code which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterfaceFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \ClassyLlama\AvaTax\Model\ResourceModel\TaxCodeSync\Collection $collection */
        $collection = $this->taxCodeSyncFactory->create()->getCollection();

        // This is needed to make sure all the attributes are properly loaded
        $collection->addFieldToSelect(['tax_code','description']);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());

        $taxCodes = [];
        /** @var \ClassyLlama\AvaTax\Model\TaxCodeSync $taxCodeModel */
        foreach ($collection as $taxCodeModel) {            
            $taxCodes[] = $taxCodeModel->getData();
        }
        $searchResults->setItems($taxCodes);
        return $searchResults;
    }
}
