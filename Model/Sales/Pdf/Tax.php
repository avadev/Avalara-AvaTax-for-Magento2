<?php

namespace ClassyLlama\AvaTax\Model\Sales\Pdf;

use ClassyLlama\AvaTax\Plugin\Model\Quote\GrandTotalDetailsSorter;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

/**
 * Class Tax
 *
 * @package ClassyLlama\AvaTax\Model\Sales\Pdf
 */
class Tax extends DefaultTotal
{
    /**
     * @var Config
     */
    protected $_taxConfig;

    /**
     * @param Data $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param Config $taxConfig
     * @param array $data
     */
    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Check if tax amount should be included to grandtotal block
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        if ($this->_taxConfig->displaySalesTaxWithGrandTotal($store)) {
            return [];
        }
        $result = [];
        $customDuty = [];
        $totals = [];
        $displayFullSummary = $this->_taxConfig->displaySalesFullSummary($store);
        foreach ($this->getFullTaxInfo() as $key => $total) {
            if ($total['title'] === GrandTotalDetailsSorter::CUSTOMS_RATE_TITLE) {
                $total['label'] = GrandTotalDetailsSorter::CUSTOMS_RATE_TITLE;
                $customDuty = [$total];
            } elseif ($displayFullSummary) {
                $totals[] = $total;
            }
        }
        $totalTax = $this->getTotalTax($customDuty[0] ?? false, $displayFullSummary);
        $result = array_merge($result, $displayFullSummary ? $customDuty : [], $totalTax, $totals);

        return $result;
    }

    /**
     * @param bool|array $customDuty
     * @param bool $displayFullSummary
     * @return array
     */
    public function getTotalTax($customDuty, $displayFullSummary = false)
    {
        $title = __('Tax');
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($customDuty) {
            if ($displayFullSummary) {
                $amount = $this->getOrder()->formatPriceTxt($this->getAmount() - $customDuty['tax_amount']);
            } else {
                $title = __('Import Fees');
            }
        }

        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];

        return [$total];
    }
}
