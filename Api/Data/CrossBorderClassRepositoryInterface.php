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

interface CrossBorderClassRepositoryInterface
{
    /**
     * Get a specific Cross-Border Class
     *
     * @param int $classId
     * @return CrossBorderClassInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($classId);

    /**
     * Create a blank Cross-Border Class
     *
     * @return CrossBorderClassInterface
     */
    public function create();

    /**
     * Save a Cross-Border Class
     *
     * @param CrossBorderClassInterface $classDataModel
     * @return CrossBorderClassInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function save($classDataModel);

    /**
     * Delete a Cross-Border Class
     *
     * @param int $classId
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function deleteById($classId);
}