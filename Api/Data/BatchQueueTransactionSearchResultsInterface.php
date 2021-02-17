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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface BatchQueueTransactionSearchResultsInterface
 *
 * @package ClassyLlama\AvaTax\Api\Data
 */
interface BatchQueueTransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return BatchQueueTransactionInterface[]
     */
    public function getItems(): array;

    /**
     * @param BatchQueueTransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items): BatchQueueTransactionSearchResultsInterface;
}
