<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use ClassyLlama\AvaTax\Model\InvoiceFactory;
use ClassyLlama\AvaTax\Model\CreditMemoFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var InvoiceFactory
     */
    protected $avataxInvoiceFactory;

    /**
     * @var CreditMemoFactory
     */
    protected $avataxCreditMemoFactory;

    /**
     * UpgradeData constructor.
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param InvoiceFactory $avataxInvoiceFactory
     * @param CreditMemoFactory $avataxCreditMemoFactory
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceFactory $avataxInvoiceFactory,
        CreditMemoFactory $avataxCreditMemoFactory
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->avataxInvoiceFactory = $avataxInvoiceFactory;
        $this->avataxCreditMemoFactory = $avataxCreditMemoFactory;
    }

    /**
     * Upgrade scripts
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.4.0', '<' )) {
            // Copy AvaTax data from Invoice and Credit Memo tables to AvaTax tables
            // Set a filter to only retrieve records with a base_avatax_tax_amount value set
            $criteria = $this->searchCriteriaBuilder
                ->addFilter('base_avatax_tax_amount', '', 'neq')
                ->create();
            $invoiceResult = $this->invoiceRepository->getList($criteria);
            $invoices = $invoiceResult->getItems();
            foreach($invoices as $invoice) {
                // Loop through retrieved invoices, creating AvaTax records
                $avaTaxRecord = $this->avataxInvoiceFactory->create();
                $avaTaxRecord->setData('parent_id', $invoice->getId());
                $avaTaxRecord->setData('is_unbalanced', $invoice->getData('avatax_is_unbalanced'));
                $avaTaxRecord->setData('base_avatax_tax_amount', $invoice->getBaseAvataxTaxAmount());
                $avaTaxRecord->save();
            }
            $creditmemoResult = $this->creditmemoRepository->getList($criteria);
            $creditmemos = $creditmemoResult->getItems();
            foreach($creditmemos as $creditmemo) {
                // Loop through retrieved credit memos, creating AvaTax records
                $avaTaxRecord = $this->avataxCreditMemoFactory->create();
                $avaTaxRecord->setData('parent_id', $creditmemo->getId());
                $avaTaxRecord->setData('is_unbalanced', $creditmemo->getData('avatax_is_unbalanced'));
                $avaTaxRecord->setData('base_avatax_tax_amount', $creditmemo->getBaseAvataxTaxAmount());
                $avaTaxRecord->save();
            }
        }
        $setup->endSetup();
    }
}