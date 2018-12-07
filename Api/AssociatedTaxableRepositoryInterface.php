<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api;

use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterface;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface AssociatedTaxableRepositoryInterface
{
    /**
     * @param integer $associatedTaxableId
     *
     * @return AssociatedTaxableInterface
     */
    public function getById($associatedTaxableId);

    /**
     * @param AssociatedTaxableInterface $associatedTaxable
     *
     * @return AssociatedTaxableInterface
     */
    public function save(AssociatedTaxableInterface $associatedTaxable);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return AssociatedTaxableSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $associatedTaxableId
     *
     * @return bool true on success
     */
    public function deleteById($associatedTaxableId);

    /**
     * @param integer $orderId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getQuoteAssociatedTaxablesForOrder($orderId);

    /**
     * @param integer $orderId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getItemAssociatedTaxablesForOrder($orderId);

    /**
     * @param integer $invoiceId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getAllAssociatedTaxablesForInvoice($invoiceId);

    /**
     * @param integer $creditMemoId
     *
     * @return AssociatedTaxableInterface[]
     */
    public function getAllAssociatedTaxablesForCreditMemo($creditMemoId);
}
