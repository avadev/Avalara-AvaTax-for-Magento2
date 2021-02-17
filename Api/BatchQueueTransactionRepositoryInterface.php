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

use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface BatchQueueTransactionRepositoryInterface
{

    /**
     * @param BatchQueueTransactionInterface $batchQueueTransaction
     *
     * @return BatchQueueTransactionInterface
     * @throws LocalizedException
     */
    public function save(BatchQueueTransactionInterface $batchQueueTransaction): BatchQueueTransactionInterface;

    /**
     *
     * @param int $id
     *
     * @return BatchQueueTransactionInterface
     * @throws LocalizedException
     */
    public function getById(int $id): BatchQueueTransactionInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param BatchQueueTransactionInterface $batchQueueTransaction
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(BatchQueueTransactionInterface $batchQueueTransaction): bool;

    /**
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $id): bool;
}
