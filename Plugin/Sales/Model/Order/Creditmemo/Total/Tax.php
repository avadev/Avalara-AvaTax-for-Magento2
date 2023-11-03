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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Plugin\Sales\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Collects credit memo taxes.
 */
class Tax
{
    /**
     * Tax calculation for creditmemo is handled in this class
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Total\Tax $subject
     * @param $result
     * @param Creditmemo $creditmemo
     * @return mixed
     */
    public function afterCollect(\Magento\Sales\Model\Order\Creditmemo\Total\Tax $subject, $result, Creditmemo $creditmemo)
    {
        $totalRefundedTax = $totalBaseRefundedTax = 0;
        $baseTotalTax = 0;
        $itemTotalTax = 0;
        $order = $creditmemo->getOrder();

        /* Condition to check if there is adjustment positive or adjustment negative */
        if ($order->getAdjustmentNegative() > 0 || $order->getAdjustmentPositive() > 0) {
            foreach ($order->getAllItems() as $item) {
                if ($item->isDummy()) {
                    continue;
                }
    
                $orderItemQty = (double)$item->getQtyInvoiced();
    
                if ($orderItemQty) {
                    /** Check refunded item tax amount */
                    $itemTaxRefunded = $item->getTaxRefunded();
                    $itemBaseTaxRefunded = $item->getBaseTaxRefunded();
                }
    
                $itemTotalTax += $itemTaxRefunded;
                $baseTotalTax += $itemBaseTaxRefunded;
            }
    
            $totalRefundedTax = $order->getTaxRefunded();
            $totalBaseRefundedTax = $order->getBaseTaxRefunded();
    
            $allowedTax = $this->calculateAllowedTax($creditmemo);
    
            $adjustmentTax = $totalRefundedTax - $itemTotalTax - $order->getShippingTaxRefunded();
            $adjustmentBaseTax = $totalBaseRefundedTax - $baseTotalTax - $order->getBaseShippingTaxRefunded();
            
            /* Condition to check whether adjustment in Tax required or not */
            if ($allowedTax == 0 && $adjustmentTax != 0) {
                $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $adjustmentTax);
                $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $adjustmentBaseTax);
    
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $adjustmentTax);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $adjustmentBaseTax);
            }
        }

        return $result;
    }

    /**
     * Calculate allowed to Credit Memo tax amount
     *
     * @param Creditmemo $creditMemo
     * @return float
     */
    private function calculateAllowedTax(Creditmemo $creditMemo): float
    {
        $invoice = $creditMemo->getInvoice();
        $order = $creditMemo->getOrder();
        if ($invoice!== null) {
            $amount = $invoice->getTaxAmount()
                - $this->calculateInvoiceRefundedAmount($invoice, CreditmemoInterface::TAX_AMOUNT);
        } else {
            $amount = $order->getTaxInvoiced() - $order->getTaxRefunded();
        }

        return (float) $amount - $creditMemo->getTaxAmount();
    }
}
