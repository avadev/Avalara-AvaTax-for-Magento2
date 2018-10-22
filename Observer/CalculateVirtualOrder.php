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
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    protected $shippingAddressAssignment;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface */
    protected $addressRepository;

    /**
     * @param QuoteSession                                   $quoteSession
     * @param \Magento\Quote\Model\Quote\TotalsCollector     $totalsCollector
     * @param CustomerRepository                             $customerRepository
     * @param CustomerSession                                $customerSession
     * @param QuoteRepository                                $quoteRepository
     * @param \Magento\Quote\Model\ShippingAddressAssignment $shippingAddressAssignment
     */
    public function __construct(
        QuoteSession $quoteSession,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        QuoteRepository $quoteRepository,
        \Magento\Quote\Model\ShippingAddressAssignment $shippingAddressAssignment,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteSession = $quoteSession;
        $this->totalsCollector = $totalsCollector;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->shippingAddressAssignment = $shippingAddressAssignment;
        $this->addressRepository = $addressRepository;
    }

    /**
     * If AvaTax GetTaxRequest failed and if configuration is set to prevent checkout, throw exception
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $quote = $this->quoteSession->getQuote();
        if (!is_null($quote) && $quote->isVirtual()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $addressId = $customer->getDefaultBilling();
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            $address = $this->addressRepository->getById($addressId);
            if ($address !== null) {
                $address = $quote->getBillingAddress()->importCustomerAddressData($address);
                $quote->setBillingAddress($address);
                $this->shippingAddressAssignment->setAddress($quote, $address, false);
                $quote->setDataChanges(true);
                $this->quoteRepository->save($quote);
            }
        }
        return $this;
    }
}
