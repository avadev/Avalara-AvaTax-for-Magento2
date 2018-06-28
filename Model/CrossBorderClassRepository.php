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
use Magento\Framework\Exception\NoSuchEntityException;
use ClassyLlama\AvaTax\Exception\InvalidTypeException;
use ClassyLlama\AvaTax\Model\CrossBorderClass\CountryLink;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\Collection as CountryLinkCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\CollectionFactory as CountryLinkCollectionFactory;

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
     * @var CountryLinkCollectionFactory
     */
    protected $countryLinkCollectionFactory;

    /**
     * @var CrossBorderClassResource
     */
    protected $crossBorderClassResource;

    /**
     * @param CrossBorderClassFactory $crossBorderClassFactory
     * @param CrossBorderClassResourceFactory $crossBorderClassResourceFactory
     * @param CountryLinkCollectionFactory
     */
    public function __construct(
        CrossBorderClassFactory $crossBorderClassFactory,
        CrossBorderClassResourceFactory $crossBorderClassResourceFactory,
        CountryLinkCollectionFactory $countryLinkCollectionFactory
    ) {
        $this->crossBorderClassFactory = $crossBorderClassFactory;
        $this->crossBorderClassResourceFactory = $crossBorderClassResourceFactory;
        $this->countryLinkCollectionFactory = $countryLinkCollectionFactory;

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

        // TODO: Implement saving of country associations

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
            throw new NoSuchEntityException(__('Cross-border Class w/ ID %1 does not exist', $classId));
        }

        return $crossBorderClass;
    }
}