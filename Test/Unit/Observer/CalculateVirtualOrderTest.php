<?php
/*
 *
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class CalculateVirtualOrderTest
 * @covers \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder
 * @package ClassyLlama\AvaTax\Observer
 */
class CalculateVirtualOrderTest extends TestCase
{
    /**
     * Mock quoteSession
     *
     * @var \Magento\Checkout\Model\Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteSession;

    /**
     * Mock totalsCollector
     *
     * @var \Magento\Quote\Model\Quote\TotalsCollector|PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollector;

    /**
     * Mock customerRepository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * Mock customerSession
     *
     * @var \Magento\Customer\Model\Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * Mock quoteRepository
     *
     * @var \Magento\Quote\Model\QuoteRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * Mock addressRepository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->any())
            ->method('isVirtual')
            ->willReturn(true);
        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->disableOriginalConstructor()
            ->getMock();
        $apiAddress = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)->disableOriginalConstructor()
            ->getMock();     
        $address->expects($this->any())
            ->method('importCustomerAddressData')
            ->willReturn($address);
        $quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($address);
        $this->quoteSession
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $this->totalsCollector = $this->getMockBuilder(\Magento\Quote\Model\Quote\TotalsCollector::class)->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)->disableOriginalConstructor()
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getDefaultBilling')
            ->willReturn(1);
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->willReturn($customer);
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()
            ->getMock();
        $this->customerSession
            ->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);
            
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository::class)->disableOriginalConstructor()
            ->getMock();
        $this->addressRepository = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)->disableOriginalConstructor()
            ->getMock();
        $this->addressRepository
            ->expects($this->any())
            ->method('getById')
            ->willReturn($apiAddress);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder::class,
            [
                'quoteSession' => $this->quoteSession,
                'totalsCollector' => $this->totalsCollector,
                'customerRepository' => $this->customerRepository,
                'customerSession' => $this->customerSession,
                'quoteRepository' => $this->quoteRepository,
                'addressRepository' => $this->addressRepository,
            ]
        );
    }

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder::execute
     */
    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->getMock();
        $this->testObject->execute($observer);
    }
}
