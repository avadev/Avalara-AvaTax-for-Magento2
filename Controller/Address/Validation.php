<?php

namespace ClassyLlama\AvaTax\Controller\Address;

use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Validation extends Action\Action
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory = null;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository = null;

    /**
     * @var OrderFactory
     */
    protected $orderFactory = null;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory = null;

    public function __construct(
        ValidationInteraction $validationInteraction,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        Context $context
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    /**
     * Test Validate Address Functionality
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return Controller\Result\Raw
     */
    public function execute()
    {
        $contents = '';

        $dataAddress = [
            'line1' => '4064 S. Lone Pine Ave.',
            'city' => 'Springfield',
            'region' => 'MO',
            'postalCode' => '65804',
        ];

        $address = $dataAddress;
        $contents .= "Data Address: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";

        $customer = $this->customerFactory->create()->load(2);

        $i = 0;
        foreach ($customer->getAddresses() as $address) {
            ++$i;
            $contents .= "Customer Address $i: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";
        }

        $serviceCustomer = $this->customerRepository->getById(2);

        $i = 0;
        foreach ($serviceCustomer->getAddresses() as $address) {
            ++$i;
            $contents .= "Service Address $i: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";
        }

        $order = $this->orderFactory->create()->load(2);

        $address = $order->getBillingAddress();
        $contents .= "Order Billing Address: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";

        $address = $order->getShippingAddress();
        $contents .= "Order Shipping Address: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";

        $i = 0;
        foreach ($order->getAddresses() as $address) {
            ++$i;
            $contents .= "Service Order Address $i: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";
        }

//        $quote = $this->quoteFactory->create()->load(2);
//
//        $address = $quote->getBillingAddress();
//        $contents .= "Quote Billing Address: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";
//
//        $address = $order->getShippingAddress();
//        $contents .= "Quote Shipping Address: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";

        $i = 0;
        foreach ($order->getAddresses() as $address) {
            ++$i;
            $contents .= "Service Quote Address $i: " . $this->validationInteraction->validateAddress($address) . "\n\n\n";
        }

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents($contents);

        return $rawResult;
    }
}