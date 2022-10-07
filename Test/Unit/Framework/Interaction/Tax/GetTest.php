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
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Tax;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ResponseTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get
 * @package ClassyLlama\AvaTax\Framework\Interaction\Tax
 */
class GetTest extends TestCase
{
    /**
     * Mock taxCalculation
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation|PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCalculation;

    /**
     * Mock interactionTax
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax|PHPUnit_Framework_MockObject_MockObject
     */
    private $interactionTax;

    /**
     * Mock getTaxResponseFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\ResponseFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $getTaxResponseFactory;

    /**
     * Mock avaTaxLogger
     *
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger|PHPUnit_Framework_MockObject_MockObject
     */
    private $avaTaxLogger;

    /**
     * Mock taxService
     *
     * @var \ClassyLlama\AvaTax\Api\RestTaxInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $taxService;

    /**
     * Mock extensionAttributesFactory
     *
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesFactory;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get
     */
    private $testObject;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->taxCalculation = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->interactionTax = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Tax::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getTaxResponseFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\ResponseFactory::class);
        $this->avaTaxLogger = $this->createMock(\ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger::class);
        $this->taxService = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestTaxInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder(\Magento\Sales\Api\Data\InvoiceExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAvataxResponse'])
            ->getMockForAbstractClass();
        $this->extensionAttributes2 = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAvataxResponse'])
            ->getMockForAbstractClass();
        $this->invoiceInterface = $this->getMockBuilder(\Magento\Sales\Api\Data\InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getTaxResponseFactory->method('create')->willReturn($this->response);
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getBaseCurrencyCode','getQuoteCurrencyCode','getStoreId','getExtensionAttributes','setExtensionAttributes','getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxQuoteDetails = $this->getMockBuilder(\Magento\Tax\Api\Data\QuoteDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseTaxQuoteDetails = $this->getMockBuilder(\Magento\Tax\Api\Data\QuoteDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignment = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::class,
            [
                'taxCalculation' => $this->taxCalculation,
                'interactionTax' => $this->interactionTax,
                'getTaxResponseFactory' => $this->getTaxResponseFactory,
                'avaTaxLogger' => $this->avaTaxLogger,
                'taxService' => $this->taxService,
                'extensionAttributesFactory' => $this->extensionAttributesFactory,
            ]
        );
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::processSalesObject
     */
    public function testProcessSalesObjectWhenGetTaxResultIsNull()
    {
        $this->expectException(\ClassyLlama\AvaTax\Exception\TaxCalculationException::class);
        $this->testObject->processSalesObject($this->invoiceInterface);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::processSalesObject
     */
    public function testProcessSalesObjectWhenException()
    {
        $e = $this->objectManager->getObject(\Exception::class);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForSalesObject')
            ->with($this->invoiceInterface)
            ->willThrowException($e);
        $this->expectException(\Exception::class);
        $this->testObject->processSalesObject($this->invoiceInterface);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::processSalesObject
     */
    public function testProcessSalesObject()
    {
        $storeId = 0;
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($this->extensionAttributes);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForSalesObject')
            ->with($this->invoiceInterface)
            ->willReturn($this->dataObject);
        $this->invoiceInterface
            ->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->extensionAttributes
            );
        $this->invoiceInterface
            ->expects($this->any())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes)
            ->willReturn($this->invoiceInterface);
        $getTaxResult = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                [\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES => true]
            )
            ->willReturn($getTaxResult);
        $array = array_merge((array) $getTaxResult, ["text" => (array) $dataObject]);
        $getTaxResult
            ->expects($this->any())
            ->method('toArray')
            ->willReturn($array);
        $avataxTaxAmount = 0;
        $unbalanced = false;
        $this->response
            ->expects($this->any())
            ->method('setIsUnbalanced')
            ->with($unbalanced)
            ->willReturn($this->response);
        $this->response
            ->expects($this->any())
            ->method('setBaseAvataxTaxAmount')
            ->with($avataxTaxAmount)
            ->willReturn($this->response);
        $this->testObject->processSalesObject($this->invoiceInterface);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::processSalesObject
     */
    public function testProcessSalesObjectWhenAvataxConnectionException()
    {
        $storeId = 0;
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($this->extensionAttributes);
        $avataxConnectionException = $this->objectManager->getObject(\ClassyLlama\AvaTax\Exception\AvataxConnectionException::class);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForSalesObject')
            ->with($this->invoiceInterface)
            ->willReturn($this->dataObject);
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                [\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES => true]
            )
            ->willThrowException($avataxConnectionException);
        $this->expectException(\ClassyLlama\AvaTax\Exception\TaxCalculationException::class);
        $this->testObject->processSalesObject($this->invoiceInterface);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::processSalesObject
     */
    public function testProcessSalesObjectWhenTaxCalculationException()
    {
        $storeId = 0;
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($this->extensionAttributes);
        $exception = $this->objectManager->getObject(\Exception::class);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForSalesObject')
            ->with($this->invoiceInterface)
            ->willReturn($this->dataObject);
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                [\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES => true]
            )
            ->willThrowException($exception);
        $this->expectException(\Exception::class);
        $this->testObject->processSalesObject($this->invoiceInterface);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTaxDetailsForQuote
     */
    public function testGetTaxDetailsForQuoteWhenGetTaxRequestIsNull()
    {
        $storeId = 0;
        $this->quote
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->expectException(\ClassyLlama\AvaTax\Exception\TaxCalculationException::class);
        $this->testObject->getTaxDetailsForQuote($this->quote, $this->taxQuoteDetails, $this->baseTaxQuoteDetails, $this->shippingAssignment);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTaxDetailsForQuote
     */
    public function testGetTaxDetailsForQuote()
    {
        $storeId = 0;
        $this->quote
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($this->extensionAttributes2);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForQuote')
            ->with($this->quote, $this->baseTaxQuoteDetails, $this->shippingAssignment)
            ->willReturn($this->dataObject);
        $this->quote
            ->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->extensionAttributes2
            );
        $this->quote
            ->expects($this->any())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes2)
            ->willReturn($this->quote);
        $getTaxResult = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId
            )
            ->willReturn($getTaxResult);
        $array = array_merge((array) $getTaxResult, ["text" => (array) $dataObject]);
        $getTaxResult
            ->expects($this->any())
            ->method('toArray')
            ->willReturn($array);
        $this->testObject->getTaxDetailsForQuote($this->quote, $this->taxQuoteDetails, $this->baseTaxQuoteDetails, $this->shippingAssignment);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTaxDetailsForQuote
     */
    public function testGetTaxDetailsForQuoteWhenAvataxConnectionException()
    {
        $storeId = 0;
        $avataxConnectionException = $this->objectManager->getObject(\ClassyLlama\AvaTax\Exception\AvataxConnectionException::class);
        $this->interactionTax
            ->expects($this->any())
            ->method('getTaxRequestForQuote')
            ->with($this->quote, $this->baseTaxQuoteDetails, $this->shippingAssignment)
            ->willReturn($this->dataObject);
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId
            )
            ->willThrowException($avataxConnectionException);
        $this->expectException(\ClassyLlama\AvaTax\Exception\TaxCalculationException::class);
        $this->testObject->getTaxDetailsForQuote($this->quote, $this->taxQuoteDetails, $this->baseTaxQuoteDetails, $this->shippingAssignment);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTaxDetailsForQuote
     */
    public function testGetTaxDetailsForQuoteWhenQuoteCurrencyIsDifferent()
    {
        $this->quote
            ->expects($this->any())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');
        $this->quote
            ->expects($this->any())
            ->method('getQuoteCurrencyCode')
            ->willReturn('INR');
        $this->testGetTaxDetailsForQuote();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTaxDetailsForQuote
     */
    public function testGetTaxDetailsForQuoteWhenGetMessagesNotNull()
    {
        $storeId = 0;
        $getTaxResult = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class)
            ->setMethods(['getMessages','toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $messages = array_merge(["text1" => $this->dataObject], ["text2" => $this->dataObject]);
        $getTaxResult
            ->expects($this->any())
            ->method('getMessages')
            ->willReturn($messages);
        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxService
            ->expects($this->any())
            ->method('getTax')
            ->with(
                $this->dataObject,
                null,
                $storeId
            )
            ->willReturn($getTaxResult);
        $array = array_merge((array) $getTaxResult, ["text" => (array) $dataObject]);
        $getTaxResult
            ->expects($this->any())
            ->method('toArray')
            ->willReturn($array);
        $this->testGetTaxDetailsForQuote();
    }
}
