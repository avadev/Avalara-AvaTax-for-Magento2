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

namespace ClassyLlama\AvaTax\Model\CrossBorderClass;

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\ProductCrossBorderDetailsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;
use ClassyLlama\AvaTax\Api\Data\ProductCrossBorderDetailsInterfaceFactory;
use Magento\Framework\Exception\InputException;

class ProductsManager
{
    /**
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductCrossBorderDetailsInterfaceFactory
     */
    protected $productCrossBorderDetailsFactory;

    /**
     * @var string
     */
    protected $destinationCountry = '';

    /**
     * @var array
     */
    protected $productCrossBorderTypes = [];

    /**
     * @var array
     */
    protected $productIdsByType = [];

    /**
     * @var ProductCrossBorderDetailsInterface[]
     */
    protected $productDetailResults = [];

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductCrossBorderDetailsInterfaceFactory $productCrossBorderDetailsFactory
     * @param string|null $destinationCountry
     * @param array|null $productCrossBorderTypes                               An array of product IDs and their cross border types, in the format [id => type]
     *
     * @throws InputException
     */
    public function __construct(
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductCrossBorderDetailsInterfaceFactory $productCrossBorderDetailsFactory,
        $destinationCountry = null,
        $productCrossBorderTypes = null
    ) {
        if (is_null($destinationCountry) || is_null($productCrossBorderTypes) || !is_array($productCrossBorderTypes)) {
            throw new InputException(__('Destination country and array of product cross border types must be provided'));
        }

        $this->crossBorderClassRepository = $crossBorderClassRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCrossBorderDetailsFactory = $productCrossBorderDetailsFactory;
        $this->destinationCountry = $destinationCountry;
        $this->productCrossBorderTypes = $productCrossBorderTypes;
    }

    /**
     * Load Cross Border Class information for the appropriate types and destination country
     *
     * @return $this
     */
    protected function loadData()
    {
        if ($this->loaded) {
            return $this;
        }

        foreach ($this->productCrossBorderTypes as $productId => $crossBorderType) {
            if (!isset($this->productIdsByType[$crossBorderType])) {
                $this->productIdsByType[$crossBorderType] = [];
            }

            $this->productIdsByType[$crossBorderType][] = $productId;
        }

        /**
         * @var array $crossBorderTypes
         */
        $crossBorderTypes = array_keys($this->productIdsByType);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('country_ids', [$this->destinationCountry])
            ->addFilter('cross_border_type_id', $crossBorderTypes, 'in')
            ->create();

        $results = $this->crossBorderClassRepository->getList($searchCriteria);

        /**
         * @var CrossBorderClassInterface $crossBorderClass
         */
        foreach ($results->getItems() as $crossBorderClass) {
            $crossBorderType = $crossBorderClass->getCrossBorderTypeId();
            if (!isset($this->productIdsByType[$crossBorderType])) {
                continue;
            }

            foreach ($this->productIdsByType[$crossBorderType] as $productId) {
                /**
                 * @var ProductCrossBorderDetailsInterface $productCrossBorderDetails
                 */
                $productCrossBorderDetails = $this->productCrossBorderDetailsFactory->create();
                $productCrossBorderDetails->setProductId($productId);
                $productCrossBorderDetails->setDestinationCountry($this->destinationCountry);
                $productCrossBorderDetails->setHsCode($crossBorderClass->getHsCode());
                $productCrossBorderDetails->setUnitName($crossBorderClass->getUnitName());
                $productCrossBorderDetails->setUnitAmountAttrCode($crossBorderClass->getUnitAmountAttrCode());
                $productCrossBorderDetails->setPrefProgramIndicator($crossBorderClass->getPrefProgramIndicator());

                $this->productDetailResults[$productId] = $productCrossBorderDetails;
            }
        }

        $this->loaded = true;

        return $this;
    }

    /**
     * Get the cross border details for a specific product ID
     *
     * @param int $productId
     * @return ProductCrossBorderDetailsInterface|null
     *
     * @throws InputException
     */
    public function getCrossBorderDetails($productId)
    {
        if (!isset($this->productCrossBorderTypes[$productId])) {
            return null;
        }

        $this->loadData();

        return (isset($this->productDetailResults[$productId])) ? $this->productDetailResults[$productId] : null;
    }
}