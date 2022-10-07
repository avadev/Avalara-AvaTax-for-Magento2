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
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TaxTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction
 */
class TaxTest extends TestCase
{
    /**
     * Mock address
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Address|PHPUnit\Framework\MockObject\MockObject
     */
    private $address;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Mock taxClassHelper
     *
     * @var \ClassyLlama\AvaTax\Helper\TaxClass|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxClassHelper;

    /**
     * Mock avaTaxLogger
     *
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger|PHPUnit\Framework\MockObject\MockObject
     */
    private $avaTaxLogger;

    /**
     * Mock metaDataObjectFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject|PHPUnit\Framework\MockObject\MockObject
     */
    private $metaDataObjectFactoryInstance;

    /**
     * Mock metaDataObjectFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $metaDataObjectFactory;

    /**
     * Mock dataObjectFactoryInstance
     *
     * @var \Magento\Framework\DataObject|PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectFactoryInstance;

    /**
     * Mock dataObjectFactory
     *
     * @var \Magento\Framework\DataObjectFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectFactory;

    /**
     * Mock customerRepository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * Mock invoiceRepository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceRepository;

    /**
     * Mock orderRepository
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepository;

    /**
     * Mock storeRepository
     *
     * @var \Magento\Store\Api\StoreRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $storeRepository;

    /**
     * Mock priceCurrency
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $priceCurrency;

    /**
     * Mock localeDate
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $localeDate;

    /**
     * Mock interactionLine
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Line|PHPUnit\Framework\MockObject\MockObject
     */
    private $interactionLine;

    /**
     * Mock taxCalculation
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxCalculation;

    /**
     * Mock restConfig
     *
     * @var \ClassyLlama\AvaTax\Helper\Rest\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $restConfig;

    /**
     * Mock customsConfig
     *
     * @var \ClassyLlama\AvaTax\Helper\CustomsConfig|PHPUnit\Framework\MockObject\MockObject
     */
    private $customsConfig;

    /**
     * Mock customer
     *
     * @var \ClassyLlama\AvaTax\Helper\Customer|PHPUnit\Framework\MockObject\MockObject
     */
    private $customer;

    /**
     * Mock customerAddressRepository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerAddressRepository;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax
     */
    private $testObject;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->address = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->createMock(\ClassyLlama\AvaTax\Helper\Config::class);
        $this->taxClassHelper = $this->createMock(\ClassyLlama\AvaTax\Helper\TaxClass::class);
        $this->avaTaxLogger = $this->createMock(\ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger::class);
        $this->metaDataObjectFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject::class);
        $this->metaDataObjectFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory::class);
        $this->metaDataObjectFactory->method('create')->willReturn($this->metaDataObjectFactoryInstance);
        $data = [
            'store_id' => 0,
            'commit' => false,
            'currency_code' => 'USD',
            'customer_code' => '3',
            'entity_use_code' => '',
            'addresses' => [
                [
                    'first_name' => 'Avalara',
                    'last_name' => 'abcde',
                    'line_1' => '5th Ave',
                    'line_2' => '',
                    'city' => 'New York',
                    'region' => 'WA',
                    'country' => 'US',
                    'zip_code' => '98001',
                    'phone' => '123456789'
                ],
                [
                    'first_name' => 'Avalara',
                    'last_name' => 'abcdef',
                    'line_1' => '5th Ave',
                    'line_2' => '',
                    'city' => 'New York',
                    'region' => 'WA',
                    'country' => 'US',
                    'zip_code' => '98002',
                    'phone' => '123456789'
                ],
            ],
            'code' => 'quote-1234',
            'type' => '0',
            'exchange_rate' => 1,
            'exchange_rate_effective_date' => "2022-08-30",
            'lines' => [
                [
                    "number"=> 1,
                    "quantity"=> 3,
                    "amount"=> 100,
                    "taxCode"=> "",
                    "itemCode"=> "ABCD1",
                    "description"=> "Text description here",
                    "ref1"=> ""
                ],
                [
                    "number"=> 1,
                    "quantity"=> 2,
                    "amount"=> 200,
                    "taxCode"=> "",
                    "itemCode"=> "ABCD2",
                    "description"=> "Text description here",
                    "ref1"=> ""
                ],
                [
                    "number"=> 3,
                    "quantity"=> 1,
                    "amount"=> 0,
                    "taxCode"=> "FR020100",
                    "itemCode"=> "Shipping",
                    "description"=> "Shipping costs",
                    "ref1"=> ""
                ]
            ],
            'purchase_order_no' => '',
            'shipping_mode' => 'flat_rate'
        ];
        $this->dataObjectFactoryInstance = new \Magento\Framework\DataObject($data);
        $this->dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->invoiceRepository = $this->createMock(\Magento\Sales\Api\InvoiceRepositoryInterface::class);
        $this->orderRepository = $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->storeRepository = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->interactionLine = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Line::class);
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingMethod',
            ])
            ->getMock();
        $this->taxCalculation = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restConfig = $this->createMock(\ClassyLlama\AvaTax\Helper\Rest\Config::class);
        $this->customsConfig = $this->createMock(\ClassyLlama\AvaTax\Helper\CustomsConfig::class);
        $this->customer = $this->createMock(\ClassyLlama\AvaTax\Helper\Customer::class);
        $this->customerAddressRepository = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Tax::class,
            [
                'address' => $this->address,
                'config' => $this->config,
                'taxClassHelper' => $this->taxClassHelper,
                'avaTaxLogger' => $this->avaTaxLogger,
                'metaDataObjectFactory' => $this->metaDataObjectFactory,
                'dataObjectFactory' => $this->dataObjectFactory,
                'customerRepository' => $this->customerRepository,
                'invoiceRepository' => $this->invoiceRepository,
                'orderRepository' => $this->orderRepository,
                'storeRepository' => $this->storeRepository,
                'priceCurrency' => $this->priceCurrency,
                'localeDate' => $this->localeDate,
                'interactionLine' => $this->interactionLine,
                'taxCalculation' => $this->taxCalculation,
                'restConfig' => $this->restConfig,
                'customsConfig' => $this->customsConfig,
                'customer' => $this->customer,
                'customerAddressRepository' => $this->customerAddressRepository,
            ]
        );
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax::getTaxRequestForQuote
     */
    public function testGetTaxRequestForQuote()
    {
        $item1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $item1
            ->expects($this->any())
            ->method('getParentCode')
            ->willReturn(null);
        $item1
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('item1');
        $item1
            ->expects($this->any())
            ->method('getType')
            ->willReturn('simple');
        $item1
            ->expects($this->any())
            ->method('getQuantity')
            ->willReturn('1');
        $item1
            ->expects($this->any())
            ->method('getUnitPrice')
            ->willReturn('1');
        $item1
            ->expects($this->any())
            ->method('getAssociatedItemCode')
            ->willReturn('');
            
        $item2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $item2
            ->expects($this->any())
            ->method('getParentCode')
            ->willReturn('item1');
        $item2
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('item2');
        $item2
            ->expects($this->any())
            ->method('getType')
            ->willReturn('simple');
        $item2
            ->expects($this->any())
            ->method('getQuantity')
            ->willReturn('1');
        $item2
            ->expects($this->any())
            ->method('getUnitPrice')
            ->willReturn('1');
        $item2
            ->expects($this->any())
            ->method('getAssociatedItemCode')
            ->willReturn('');

        $item3 = $this->getMockBuilder(
                \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
            )->disableOriginalConstructor()->getMockForAbstractClass();
        $item3
            ->expects($this->any())
            ->method('getParentCode')
            ->willReturn(null);
        $item3
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('item3');
        $item3
            ->expects($this->any())
            ->method('getType')
            ->willReturn('simple');
        $item3
            ->expects($this->any())
            ->method('getQuantity')
            ->willReturn('1');
        $item3
            ->expects($this->any())
            ->method('getUnitPrice')
            ->willReturn('1');
        $item3
            ->expects($this->any())
            ->method('getAssociatedItemCode')
            ->willReturn('');

        $taxQuoteDetails = $this->getMockBuilder(\Magento\Tax\Api\Data\QuoteDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $taxQuoteDetails
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$item1, $item2, $item3]);
        $shippingAssignment = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->taxCalculation
            ->expects($this->any())
            ->method('getKeyedItems')
            ->willReturn([$item1, $item2, $item3]);
        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $shippingAssignment
            ->expects($this->any())
            ->method('getShipping')
            ->willReturn($shippingAddress);
        $addressInterface = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
       
        $this->address
            ->expects($this->any())
            ->method('getAddress')
            ->willReturn($addressInterface);
        $this->localeDate->expects($this->any())
            ->method('getDefaultTimezone')
            ->willReturn('UTC');
        $this->localeDate->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('UTC');
        $storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeRepository->expects($this->any())
            ->method('getById')
            ->with(0)
            ->willReturn($storeInterface);
        $currencyInterface = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quote->expects($this->any())
            ->method('getCurrency')
            ->willReturn($currencyInterface);
        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($storeInterface);
        
        $objAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($objAddress);
        //$objAddress->expects($this->once())->method('getShippingMethod')->willReturn('free');
        $this->quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($objAddress);
       
        $this->customerInterface = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->expects($this->any())
            ->method('getCustomer')
            ->willReturn($this->customerInterface);  
        /*
        $this->testObject->getTaxRequestForQuote(
                $this->quote,
                $taxQuoteDetails,
                $shippingAssignment
            );
        */
    }
}
