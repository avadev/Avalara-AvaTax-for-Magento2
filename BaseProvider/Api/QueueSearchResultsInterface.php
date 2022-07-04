<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\BaseProvider\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Queue job search results.
 * @api
 */
interface QueueSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Queue job list.
     *
     * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface[]
     */
    public function getItems();

    /**
     * Set Queue job list.
     *
     * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
