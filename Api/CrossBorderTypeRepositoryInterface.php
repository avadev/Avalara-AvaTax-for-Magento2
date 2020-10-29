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

namespace ClassyLlama\AvaTax\Api;

interface CrossBorderTypeRepositoryInterface
{

    /**
     * Save CrossBorderType
     *
     * @param \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType
     *
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType);

    /**
     * Retrieve CrossBorderType
     *
     * @param string $id
     *
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve CrossBorderType matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete CrossBorderType
     *
     * @param \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface $crossBorderType);

    /**
     * Delete CrossBorderType by ID
     *
     * @param string $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
