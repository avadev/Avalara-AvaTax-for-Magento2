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

namespace ClassyLlama\AvaTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
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
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getData('order');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');

        $quoteItemsByQuoteItemId = [];
        $quoteItems = $quote->getItems();

        $this->extensionAttributeMerger->copyAttributes($quote, $order, ['avatax_response']);

        // Can't work with the data unless it's an array
        if (!is_array($quoteItems)) {
            return;
        }

        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $quoteItems */
        foreach ($quoteItems as $quoteItem) {
            $quoteItemsByQuoteItemId[$quoteItem->getItemId()] = $quoteItem;
        }

        foreach ($order->getItems() as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            // If we don't have the quote item, then ignore, we can't do anything with this order item
            if (!isset($quoteItemsByQuoteItemId[$orderItem->getQuoteItemId()])) {
                continue;
            }

            $this->extensionAttributeMerger->copyAttributes(
                $quoteItemsByQuoteItemId[$orderItem->getQuoteItemId()],
                $orderItem
            );
        }
    }
}
