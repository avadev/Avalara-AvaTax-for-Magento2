<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as QuoteSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;

class CalculateVirtualOrder implements ObserverInterface
{
    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @param QuoteSession                                     $quoteSession
     * @param \Magento\Quote\Model\Quote\TotalsCollector       $totalsCollector
     * @param CustomerRepository                               $customerRepository
     * @param CustomerSession                                  $customerSession
     * @param QuoteRepository                                  $quoteRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        QuoteSession $quoteSession,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        QuoteRepository $quoteRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteSession = $quoteSession;
        $this->totalsCollector = $totalsCollector;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Use Avatax to calculate tax for virtual orders
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $quote = $this->quoteSession->getQuote();
        if (!is_null($quote) && $quote->isVirtual()) {
            try {
                $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
                $addressId = $customer->getDefaultBilling();
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                $address = $this->addressRepository->getById($addressId);
                $address = $quote->getBillingAddress()->importCustomerAddressData($address);
                if ($address !== null) {
                    $quote->setBillingAddress($address);
                    $quote->setDataChanges(true);
                    $this->quoteRepository->save($quote);
                }
            } catch (LocalizedException $e) {
                return $this;
            } catch (\Exception $e) {
                return $this;
            } 
        }
        return $this;
    }
}
