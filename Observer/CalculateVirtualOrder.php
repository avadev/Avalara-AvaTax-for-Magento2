<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class CalculateVirtualOrder implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * If AvaTax GetTaxRequest failed and if configuration is set to prevent checkout, throw exception
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $quote = $this->session->getQuote();
        if (!is_null($quote) && $quote->isVirtual()) {
            // Only calculate sales tax via this method if the order is virtual so as not to inadvertently raise the
            // getTax count on physical or hybrid orders
            
            // Add logic here to calculate sales tax, possibly just run $quote->collectTotals();
        }
        return $this;
    }
}
