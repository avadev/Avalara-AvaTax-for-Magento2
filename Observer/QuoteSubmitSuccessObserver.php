<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Observer;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterfaceFactory as AssociatedTaxableFactory;
use ClassyLlama\AvaTax\Api\Data\AssociatedTaxableInterface;
use ClassyLlama\AvaTax\Model\Tax\AssociatedTaxableRepository;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class QuoteSubmitSuccessObserver implements ObserverInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\AssociatedTaxable
     */
    protected $associatedTaxableHelper;

    /**
     * @var AssociatedTaxableFactory
     */
    protected $associatedTaxableFactory;

    /**
     * @var AssociatedTaxableRepository
     */
    protected $associatedTaxableRepository;

    /**
     * @var AvaTaxLogger
     */
    protected $avataxLogger;

    /**
     * QuoteSubmitSuccessObserver constructor.
     *
     * @param AssociatedTaxableFactory    $associatedTaxableInterfaceFactory
     * @param AssociatedTaxableRepository $associatedTaxableRepository
     * @param AvaTaxLogger                $logger
     */
    public function __construct(
        AssociatedTaxableFactory $associatedTaxableInterfaceFactory,
        AssociatedTaxableRepository $associatedTaxableRepository,
        AvaTaxLogger $logger,
        \ClassyLlama\AvaTax\Helper\AssociatedTaxable $associatedTaxableHelper
    ) {
        $this->associatedTaxableFactory = $associatedTaxableInterfaceFactory;
        $this->associatedTaxableRepository = $associatedTaxableRepository;
        $this->avataxLogger = $logger;
        $this->associatedTaxableHelper = $associatedTaxableHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        /** @var Order $order */
        $order = $observer->getOrder();

        $quoteItemIdOrderItemMap = [];
        foreach ($order->getItems() as $orderItem) {
            $quoteItemIdOrderItemMap[$orderItem->getQuoteItemId()] = $orderItem;
        }
        /** @var Quote\Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            // item-level associated taxables
            $associatedTaxables = $quoteItem->getAssociatedTaxables();
            if ($associatedTaxables !== null) {
                foreach ($associatedTaxables as $associatedTaxableData) {
                    $associatedTaxableData = $this->associatedTaxableHelper->updateData(
                        $associatedTaxableData,
                        $quoteItem->getStoreId()
                    );
                    /** @var AssociatedTaxableInterface $associatedTaxable */
                    $associatedTaxable = $this->associatedTaxableFactory->create(
                        [
                            'data' => $associatedTaxableData
                        ]
                    );
                    $associatedTaxable->setInvoiceId(null);
                    if (isset($quoteItemIdOrderItemMap[$quoteItem->getId()])) {
                        /** @var Order\Item $orderItem */
                        $orderItem = $quoteItemIdOrderItemMap[$quoteItem->getId()];
                        $associatedTaxable->setOrderItemId((int)$orderItem->getId());
                        $associatedTaxable->setOrderId($order->getId());
                        try {
                            $this->associatedTaxableRepository->save($associatedTaxable);
                        } catch (\Exception $e) {
                            $this->avataxLogger->error(
                                "There was a problem saving associated taxable item information for order {$order->getId()}"
                            );
                        }
                    }
                }
            }
        }

        if (!$quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            // Quote-level associated taxables
            $associatedTaxables = $shippingAddress->getAssociatedTaxables();
            if ($associatedTaxables !== null) {
                foreach ($associatedTaxables as $associatedTaxableData) {
                    $associatedTaxableData = $this->associatedTaxableHelper->updateData(
                        $associatedTaxableData,
                        $quote->getStoreId()
                    );
                    $associatedTaxable = $this->associatedTaxableFactory->create(
                        [
                            'data' => $associatedTaxableData
                        ]
                    );
                    $associatedTaxable->setInvoiceId(null);
                    $associatedTaxable->setOrderItemId(null);
                    $associatedTaxable->setOrderId($order->getId());
                    try {
                        $this->associatedTaxableRepository->save($associatedTaxable);
                    } catch (\Exception $e) {
                        $this->avataxLogger->error(
                            "There was a problem saving associated taxable quote information for order {$order->getId()}"
                        );
                    }
                }
            }
        }
    }
}
