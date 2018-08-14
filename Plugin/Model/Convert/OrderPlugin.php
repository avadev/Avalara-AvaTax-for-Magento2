<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model\Convert;

class OrderPlugin
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger
     */
    protected $extensionAttributeMerger;

    /**
     * @param \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger $extensionAttributeMerger
     */
    public function __construct(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger $extensionAttributeMerger)
    {
        $this->extensionAttributeMerger = $extensionAttributeMerger;
    }

    /**
     * @param \Magento\Sales\Model\Convert\Order $subject
     * @param callable                           $proceed
     * @param \Magento\Sales\Model\Order\Item    $item
     *
     * @return mixed
     */
    public function aroundItemToInvoiceItem(
        \Magento\Sales\Model\Convert\Order $subject,
        callable $proceed,
        \Magento\Sales\Model\Order\Item $item
    )
    {
        $invoiceItem = $proceed($item);

        $this->extensionAttributeMerger->copyAttributes($item, $invoiceItem);

        return $invoiceItem;
    }

    /**
     * @param \Magento\Sales\Model\Convert\Order $subject
     * @param callable                           $proceed
     * @param \Magento\Sales\Model\Order\Item    $item
     *
     * @return mixed
     */
    public function aroundItemToCreditmemoItem(
        \Magento\Sales\Model\Convert\Order $subject,
        callable $proceed,
        \Magento\Sales\Model\Order\Item $item
    )
    {
        $invoiceItem = $proceed($item);

        $this->extensionAttributeMerger->copyAttributes($item, $invoiceItem);

        return $invoiceItem;
    }
}