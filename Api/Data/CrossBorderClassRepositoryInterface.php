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

namespace ClassyLlama\AvaTax\Api\Data;

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;
use ClassyLlama\AvaTax\Exception\InvalidTypeException;

interface CrossBorderClassRepositoryInterface
{
    /**
     * Get a specific Cross Border Class
     *
     * @param int $classId
     * @return CrossBorderClassInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws InvalidTypeException
     */
    public function getById($classId);

    /**
     * Get list of Cross Border Classes
     *
     * @param $criteria \Magento\Framework\Api\SearchCriteriaInterface
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderClassSearchResultsInterface
     */
    public function getList($criteria);

    /**
     * Create a blank Cross Border Class
     *
     * @return CrossBorderClassInterface
     * @throws InvalidTypeException
     */
    public function create();

    /**
     * Save a Cross Border Class
     *
     * @param CrossBorderClassInterface $class
     * @return CrossBorderClassInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws InvalidTypeException
     * @throws \Exception
     */
    public function save($class);

    /**
     * Delete a Cross Border Class
     *
     * @param int $classId
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function deleteById($classId);

    /**
     * Get countries associated with a class
     *
     * @param int $classId
     * @return string[]
     */
    public function getCountriesForClass($classId);

    /**
     * Get countries associated with multiple classes
     *
     * @param int[] $classIds
     * @return array
     */
    public function getCountriesForClasses($classIds);

    /**
     * Add associated countries to a class in a standard format
     *
     * @param CrossBorderClassInterface|\Magento\Framework\Api\Search\DocumentInterface $class
     * @param null|string[]|\ClassyLlama\AvaTax\Model\CrossBorderClass\CountryLink[] $countries
     * @return CrossBorderClassInterface
     */
    public function addCountriesToClass($class, $countries = null);
}