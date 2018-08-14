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

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;
use ClassyLlama\AvaTax\Model\CrossBorderClassFactory;
use ClassyLlama\AvaTax\Model\CrossBorderClass;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClassFactory as CrossBorderClassResourceFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass as CrossBorderClassResource;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Collection as CrossBorderClassCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CollectionFactory as CrossBorderClassCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use ClassyLlama\AvaTax\Exception\InvalidTypeException;
use ClassyLlama\AvaTax\Model\CrossBorderClass\CountryLink;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\Collection as CountryLinkCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\CollectionFactory as CountryLinkCollectionFactory;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassSearchResultsInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class CrossBorderClassRepository implements \ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface
{
    /**
     * @var CrossBorderClassFactory
     */
    protected $crossBorderClassFactory;

    /**
     * @var CrossBorderClassResourceFactory
     */
    protected $crossBorderClassResourceFactory;

    /**
     * @var CrossBorderClassCollectionFactory
     */
    protected $crossBorderClassCollectionFactory;

    /**
     * @var CountryLinkCollectionFactory
     */
    protected $countryLinkCollectionFactory;

    /**
     * @var CrossBorderClassResource
     */
    protected $crossBorderClassResource;

    /**
     * @var CrossBorderClassSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param CrossBorderClassFactory $crossBorderClassFactory
     * @param CrossBorderClassResourceFactory $crossBorderClassResourceFactory
     * @param CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory
     * @param CountryLinkCollectionFactory $countryLinkCollectionFactory
     * @param CrossBorderClassSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CrossBorderClassFactory $crossBorderClassFactory,
        CrossBorderClassResourceFactory $crossBorderClassResourceFactory,
        CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory,
        CountryLinkCollectionFactory $countryLinkCollectionFactory,
        CrossBorderClassSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->crossBorderClassFactory = $crossBorderClassFactory;
        $this->crossBorderClassResourceFactory = $crossBorderClassResourceFactory;
        $this->crossBorderClassCollectionFactory = $crossBorderClassCollectionFactory;
        $this->countryLinkCollectionFactory = $countryLinkCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;

        $this->crossBorderClassResource = $this->crossBorderClassResourceFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getById($classId)
    {
        $crossBorderClass = $this->loadModel($classId);

        if (!($crossBorderClass instanceof CrossBorderClassInterface)) {
            throw new InvalidTypeException(__('CrossBorderClassRepository implementation must be changed if CrossBorderClassInterface implementation changes'));
        }

        $this->addCountriesToClass($crossBorderClass);

        return $crossBorderClass;
    }

    /**
     * @inheritdoc
     */
    public function getList($criteria)
    {
        /**
         * @var CrossBorderClassCollection $collection
         */
        $collection = $this->crossBorderClassCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $items = $collection->getItems();
        $classIds = array_keys($items);
        $countriesByClass = $this->getCountriesForClasses($classIds);
        /**
         * @var CrossBorderClassInterface $crossBorderClass
         */
        foreach ($collection as $crossBorderClass) {
            if (isset($countriesByClass[$crossBorderClass->getId()])) {
                $this->addCountriesToClass($crossBorderClass, $countriesByClass[$crossBorderClass->getId()]);
            }
        }

        /**
         * @var CrossBorderClassSearchResultsInterface $searchResults
         */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        $result = $this->crossBorderClassFactory->create();

        if (!($result instanceof CrossBorderClassInterface)) {
            throw new InvalidTypeException(__('CrossBorderClassRepository implementation must be changed if CrossBorderClassInterface implementation changes'));
        }

        return $result;
    }

    /**
     * @inheritdoc
     *
     */
    public function save($class)
    {
        if (!($class instanceof CrossBorderClass)) {
            throw new InvalidTypeException(__('CrossBorderClassRepository implementation must be changed if CrossBorderClassInterface implementation changes'));
        }

        $classResource = $this->crossBorderClassResourceFactory->create();
        $classResource->save($class);

        return $class;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($classId)
    {
        $crossBorderClass = $this->loadModel($classId);
        $this->crossBorderClassResource->delete($crossBorderClass);
    }

    /**
     * @inheritdoc
     */
    public function getCountriesForClass($classId)
    {
        /**
         * @var CountryLinkCollection $countryLinkCollection
         */
        $countryLinkCollection = $this->countryLinkCollectionFactory->create();
        $countryLinkCollection->addFieldToFilter('class_id', $classId);

        /**
         * @var CountryLink $countryLink
         */
        $result = [];
        foreach ($countryLinkCollection as $countryLink) {
            $result[] = $countryLink->getCountryId();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getCountriesForClasses($classIds)
    {
        /**
         * @var CountryLinkCollection $countryLinkCollection
         */
        $countryLinkCollection = $this->countryLinkCollectionFactory->create();
        $countryLinkCollection->addFieldToFilter('class_id', ['in' => $classIds]);

        /**
         * @var CountryLink $countryLink
         */
        $result = [];
        foreach ($countryLinkCollection as $countryLink) {
            if (!isset($result[$countryLink->getClassId()])) {
                $result[$countryLink->getClassId()] = [];
            }

            $result[$countryLink->getClassId()][] = $countryLink->getCountryId();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addCountriesToClass($class, $countries = null)
    {
        if (is_null($countries)) {
            $countries = $this->getCountriesForClass($class->getId());
        }

        $result = [];
        foreach ($countries as $country) {
            if ($country instanceof CountryLink) {
                $country = $country->getCountryId();
            }

            $result[] = $country;
        }

        $class->setDestinationCountries($result);

        return $class;
    }

    /**
     * Load the full model for a class
     *
     * @param int $classId
     * @return CrossBorderClass
     * @throws NoSuchEntityException
     */
    protected function loadModel($classId)
    {
        /**
         * @var CrossBorderClass $crossBorderClass
         */
        $crossBorderClass = $this->crossBorderClassFactory->create();

        $this->crossBorderClassResource->load($crossBorderClass, $classId);

        if (!$crossBorderClass->getId()) {
            throw new NoSuchEntityException(__('Cross Border Class w/ ID %1 does not exist', $classId));
        }

        return $crossBorderClass;
    }
}