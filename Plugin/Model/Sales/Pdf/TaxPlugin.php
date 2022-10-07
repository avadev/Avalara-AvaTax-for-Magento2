<?php

namespace ClassyLlama\AvaTax\Plugin\Model\Sales\Pdf;

use ClassyLlama\AvaTax\Plugin\Model\Quote\GrandTotalDetailsSorter;
use Magento\Framework\Locale\FormatInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Pdf\Tax;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelperConfig;

/**
 * Class TaxPlugin
 *
 * @package ClassyLlama\AvaTax\Plugin\Model\Sales\Pdf
 */
class TaxPlugin
{
    const TOTAL_TAX_LABEL = 'Tax';
    const TOTAL_TAX_CUSTOM_LABEL = 'Import Fees';

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var AvaTaxHelperConfig
     */
    private $avaTaxHelperConfig;

    /**
     * TaxPlugin constructor.
     *
     * @param FormatInterface $format
     * @param Config $taxConfig
     * @param AvaTaxHelperConfig $avaTaxHelperConfig
     */
    public function __construct(
        FormatInterface $format,
        Config $taxConfig,
        AvaTaxHelperConfig $avaTaxHelperConfig
    ) {
        $this->format = $format;
        $this->taxConfig = $taxConfig;
        $this->avaTaxHelperConfig = $avaTaxHelperConfig;
    }

    /**
     * @param Tax $subject
     * @param $totals
     * @return array
     */
    public function afterGetTotalsForDisplay(Tax $subject, $totals)
    {
        if (empty($totals)) {
            return $totals;
        }
        $totalTaxKey = array_search($this->getTotalTaxLabel($subject->getTitle(), $subject),
            array_column($totals, 'label'));
        $totalTax = $totals[$totalTaxKey];
        unset($totals[$totalTaxKey]);


        $store = $subject->getOrder()->getStore();
        $taxTitle = self::TOTAL_TAX_LABEL;
        $taxIncluded = $this->avaTaxHelperConfig->getTaxationPolicy($store);
        if ($taxIncluded)
            $taxTitle .= " (".AvaTaxHelperConfig::XML_SUFFIX_AVATAX_TAX_INCLUDED.")";
        if ($this->taxConfig->displaySalesFullSummary($store)) {

            $customDutyKey = array_search(GrandTotalDetailsSorter::CUSTOMS_RATE_TITLE,
                array_column($totals, 'title'));
            $customDuty = $totals[$customDutyKey];
            $customDuty['label'] = $this->getTotalTaxLabel(GrandTotalDetailsSorter::CUSTOMS_RATE_TITLE, $subject);
            unset($totals[$customDutyKey]);


            $amount = $subject->getOrder()->formatPriceTxt($this->format->getNumber($totalTax['amount'])
                - $this->format->getNumber($customDuty['amount']));
            if ($subject->getAmountPrefix()) {
                $amount = $subject->getAmountPrefix() . $amount;
            }

            $totalTax['label'] = $this->getTotalTaxLabel($taxTitle, $subject);
            $totalTax['amount'] = $amount;

            $result = array_merge([$customDuty], [$totalTax], $totals);
        } else {
            $customDutyKey = array_search(GrandTotalDetailsSorter::CUSTOMS_RATE_TITLE,
                array_column($subject->getFullTaxInfo(), 'title'));
            $totalTax['label'] = $this->getTotalTaxLabel(is_numeric($customDutyKey)
                ? self::TOTAL_TAX_CUSTOM_LABEL : $taxTitle, $subject);
            $result = [$totalTax];
        }

        return $result;
    }

    /**
     * @param $title
     * @param $subject
     * @return string
     */
    public function getTotalTaxLabel($title, $subject)
    {
        $title = __($title);
        if ($subject->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        return $label;
    }
}
