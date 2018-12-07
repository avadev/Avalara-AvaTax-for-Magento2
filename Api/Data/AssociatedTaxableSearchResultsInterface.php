<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AssociatedTaxableSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return AssociatedTaxableInterface[]
     */
    public function getItems();

    /**
     * @param AssociatedTaxableInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
