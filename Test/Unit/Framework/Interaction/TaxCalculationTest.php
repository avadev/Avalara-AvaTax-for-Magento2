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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateExtensionFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Config;
use Magento\Framework\Api\DataObjectHelper;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelper;
use Magento\Checkout\Model\Cart as Cart;
use Magento\Quote\Model\Quote as Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
/**
 * Class TaxCalculationTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation
 * @package ClassyLlama\AvaTax\Framework\Interaction
 */
class TaxCalculationTest extends TestCase
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AppliedTaxInterfaceFactory
     */
    protected $appliedTaxDataObjectFactory;

    /**
     * @var AppliedTaxRateInterfaceFactory
     */
    protected $appliedTaxRateDataObjectFactory;

    /**
     * @var QuoteDetailsItemExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var AppliedTaxRateExtensionFactory
     */
    protected $appliedTaxRateExtensionFactory;

    /**
     * Rate that will be used instead of 0, as using 0 causes tax rates to not save
     */
    const DEFAULT_TAX_RATE = -0.001;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->calculation = $this->getMockBuilder(Calculation::class)->disableOriginalConstructor()
                                    ->getMock();
        $this->calculatorFactory = $this->getMockBuilder(CalculatorFactory::class)->disableOriginalConstructor()
                                        ->getMock();
        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()
                                ->getMock();
        $this->taxDetailsDataObject = $this->getMockBuilder(TaxDetailsInterface::class)->disableOriginalConstructor()
                                ->getMock();
        $this->taxDetailsDataObject
            ->expects($this->any())
            ->method('setItems')
            ->willReturn($this->taxDetailsDataObject);
        $this->taxDetailsDataObjectFactory = $this->getMockBuilder(TaxDetailsInterfaceFactory::class)->disableOriginalConstructor()
                                                    ->getMock();
        $this->taxDetailsDataObjectFactory->method('create')->willReturn($this->taxDetailsDataObject);
        
        $this->taxDetailsItemDataObject = $this->getMockBuilder(TaxDetailsItemInterface::class)->disableOriginalConstructor()
                                                        ->getMock();
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setPrice')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setPriceInclTax')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setRowTotal')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setRowTotalInclTax')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setRowTax')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setCode')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setType')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setDiscountTaxCompensationAmount')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setAssociatedItemCode')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setTaxPercent')
            ->willReturn($this->taxDetailsItemDataObject);
        $this->taxDetailsItemDataObject
            ->expects($this->any())
            ->method('setAppliedTaxes')
            ->willReturn($this->taxDetailsItemDataObject);

        $this->taxDetailsItemDataObjectFactory = $this->getMockBuilder(TaxDetailsItemInterfaceFactory::class)->disableOriginalConstructor()
                                                        ->getMock();
        $this->taxDetailsItemDataObjectFactory->method('create')->willReturn($this->taxDetailsItemDataObject);
         
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)->disableOriginalConstructor()
                                    ->getMock();
        $this->taxClassManagement = $this->getMockBuilder(TaxClassManagementInterface::class)->disableOriginalConstructor()
                                            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)->disableOriginalConstructor()
                                        ->getMock();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)->disableOriginalConstructor()
                                    ->getMock();
        $this->priceCurrency
            ->expects($this->any())
            ->method('convert')
            ->willReturn(10);
        $this->appliedTaxDataObjectFactory = $this->getMockBuilder(AppliedTaxInterfaceFactory::class)->disableOriginalConstructor()
                                                    ->getMock();
        $this->appliedTaxRateDataObjectFactory = $this->getMockBuilder(AppliedTaxRateInterfaceFactory::class)->disableOriginalConstructor()
                                                        ->getMock();
        $this->extensionFactory = $this->getMockBuilder(QuoteDetailsItemExtensionFactory::class)->disableOriginalConstructor()
                                        ->getMock();
        $this->appliedTaxRateExtensionFactory = $this->getMockBuilder(AppliedTaxRateExtensionFactory::class)->disableOriginalConstructor()
                                                        ->getMock();
        $this->avaTaxHelper = $this->getMockBuilder(AvaTaxHelper::class)->disableOriginalConstructor()
                                                        ->getMock();
        $this->avaTaxHelper
            ->expects($this->any())
            ->method('getTaxationPolicy')
            ->willReturn(true);
        $this->cart = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();
        
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->cart->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::class,
                [
                    "calculation" => $this->calculation,
                    "calculatorFactory" => $this->calculatorFactory,
                    "config" => $this->config,
                    "taxDetailsDataObjectFactory" => $this->taxDetailsDataObjectFactory,
                    "taxDetailsItemDataObjectFactory" => $this->taxDetailsItemDataObjectFactory,
                    "storeManager" => $this->storeManager,
                    "taxClassManagement" => $this->taxClassManagement,
                    "dataObjectHelper" => $this->dataObjectHelper,
                    "priceCurrency" => $this->priceCurrency,
                    "appliedTaxDataObjectFactory" => $this->appliedTaxDataObjectFactory,
                    "appliedTaxRateDataObjectFactory" => $this->appliedTaxRateDataObjectFactory,
                    "extensionFactory" => $this->extensionFactory,
                    "appliedTaxRateExtensionFactory" => $this->appliedTaxRateExtensionFactory,
                    "avaTaxHelper" => $this->avaTaxHelper,
                    "cart"=>$this->cart
                ]
            );
    }

    /**
     * tests calculateTaxDetails
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTaxDetails
     */
    public function testCalculateTaxDetails()
    {
        $item1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();

        $extensionAttr1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr1
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('abc');

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
        $item1    
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr1);
        
        $extensionAttr2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr2
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('pqr');
        $item2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();
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
        $item2    
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr2);
        
        $extensionAttr3 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr3
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('xyz');
        $item3 = $this->getMockBuilder(
                \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
            )->disableOriginalConstructor()->getMock();
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
        $item3    
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr3);

        $taxQuoteDetails = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsInterface::class
        )->disableOriginalConstructor()->getMock();
        $taxQuoteDetails
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$item1, $item2, $item3]);
        
        $getTaxResult = $this->getMockBuilder(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class
        )->disableOriginalConstructor()->getMock();

        $taxLine = $this->objectManager->getObject(
                \Magento\Framework\DataObject::class,
                []
            );
        $taxLine->setTax(10);
        $taxLine->setExemptAmount(5);
        $lineItemDetailData = [
            "juris_code" => "A",
            "juris_name" => "A",
            "juris_type" => "A",
            "tax_type" => "Customs",
            "tax_sub_type_id" => "A",
            "tax_name" => "A",
            "rate" => 2,
            "taxable_amount" => 10,
            "tax_calculated" => 11,
            "tax" => 11,

        ] ;
        $lineItemDetail1 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail1->setData($lineItemDetailData);
        $lineItemDetail2 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail2->setData($lineItemDetailData);
        $taxLine->setDetails([$lineItemDetail1, $lineItemDetail2]);
        $getTaxResult
            ->expects($this->any())
            ->method('getTaxLine')
            ->willReturn($taxLine);
        $getTaxResult
            ->expects($this->any())
            ->method('getLineRate')
            ->willReturn(2.0);

        $scope = $this->getMockBuilder( \Magento\Framework\App\ScopeInterface::class )->disableOriginalConstructor()->getMock();

        $itemMock = $this->getMockBuilder(QuoteItem::class)->disableOriginalConstructor()->getMock();
        
        $itemMock
            ->expects($this->any())
            ->method('getSku')
            ->willReturn('abc');
        
        $itemMock->expects($this->any())
        ->method('setData')
        ->willReturn($itemMock);
        $itemMock->expects($this->any())
        ->method('save')
        ->willReturn($itemMock);

        $visibleItems = [
            11 => $itemMock,
        ];
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($visibleItems);  

        $this->testObject->calculateTaxDetails(
            $taxQuoteDetails,
            $getTaxResult,
            false,
            $scope
        );
        
    }

    /**
     * tests calculateTaxDetails
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTaxDetails
     */
    public function testCalculateTaxDetails2()
    {
        $item1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();
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
        )->disableOriginalConstructor()->getMock();
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
            )->disableOriginalConstructor()->getMock();
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

        $taxQuoteDetails = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsInterface::class
        )->disableOriginalConstructor()->getMock();
        $taxQuoteDetails
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$item1, $item2]);
        
        $getTaxResult = $this->getMockBuilder(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class
        )->disableOriginalConstructor()->getMock();

        $taxLine = $this->objectManager->getObject(
                \Magento\Framework\DataObject::class,
                []
            );
        $taxLine->setTax(10);
        $taxLine->setExemptAmount(5);
        $lineItemDetailData = [
            "juris_code" => "A",
            "juris_name" => "A",
            "juris_type" => "A",
            "tax_type" => "Customs",
            "tax_sub_type_id" => "A",
            "tax_name" => "A",
            "rate" => 2,
            "taxable_amount" => 10,
            "tax_calculated" => 11,
            "tax" => 11,

        ] ;
        $lineItemDetail1 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail1->setData($lineItemDetailData);
        $lineItemDetail2 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail2->setData($lineItemDetailData);
        $taxLine->setDetails([$lineItemDetail1, $lineItemDetail2]);
        $getTaxResult
            ->expects($this->any())
            ->method('getTaxLine')
            ->willReturn(null);
        $getTaxResult
            ->expects($this->any())
            ->method('getLineRate')
            ->willReturn(2.0);

        $scope = $this->getMockBuilder(
            \Magento\Framework\App\ScopeInterface::class
        )->disableOriginalConstructor()->getMock();
        $itemMock = $this->getMockBuilder(QuoteItem::class)->disableOriginalConstructor()->getMock();
        
        $itemMock
            ->expects($this->any())
            ->method('getSku')
            ->willReturn('abc');
        
        $itemMock->expects($this->any())
        ->method('setData')
        ->willReturn($itemMock);
        $itemMock->expects($this->any())
        ->method('save')
        ->willReturn($itemMock);

        $visibleItems = [
            11 => $itemMock,
        ];
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($visibleItems);     
        $this->testObject->calculateTaxDetails(
            $taxQuoteDetails,
            $getTaxResult,
            false,
            $scope
        );
    }

    /**
     * tests calculateTaxDetails
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTaxDetails
     */
    public function testCalculateTaxDetails3()
    {
        $item1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();
            
        $extensionAttr1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr1
            ->expects($this->any())
            ->method('getTotalQuantity')
            ->willReturn(null);
        $extensionAttr1
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('abc');
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
        $item1
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr1);
            
        $extensionAttr2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();    
        $extensionAttr2
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('pqr');
        $item2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();
        
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
        $item2
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr2);
        $extensionAttr3 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr3
            ->expects($this->any())
            ->method('getAvataxItemCode')
            ->willReturn('xyz');
        $extensionAttr3
            ->expects($this->any())
            ->method('getTotalQuantity')
            ->willReturn(1);
        $item3 = $this->getMockBuilder(
                \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
            )->disableOriginalConstructor()->getMock();
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
        $item3
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr3);

        $taxQuoteDetails = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsInterface::class
        )->disableOriginalConstructor()->getMock();
        $taxQuoteDetails
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$item1, $item2, $item3]);
        
        $getTaxResult = $this->getMockBuilder(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class
        )->disableOriginalConstructor()->getMock();

        $taxLine = $this->objectManager->getObject(
                \Magento\Framework\DataObject::class,
                []
            );
        $taxLine->setTax(0);
        $taxLine->setExemptAmount(0);
        $lineItemDetailData = [
            "juris_code" => "A",
            "juris_name" => "A",
            "juris_type" => "A",
            "tax_type" => "A",
            "tax_sub_type_id" => "A",
            "tax_name" => "A",
            "rate" => 0,
            "taxable_amount" => 10,
            "tax_calculated" => 11,
            "tax" => 0,

        ] ;
        $lineItemDetail1 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail1->setData($lineItemDetailData);
        $lineItemDetail2 = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            []
        );
        $lineItemDetail2->setData($lineItemDetailData);
        $taxLine->setDetails([$lineItemDetail1, $lineItemDetail2]);
        $getTaxResult
            ->expects($this->any())
            ->method('getTaxLine')
            ->willReturn($taxLine);
        $getTaxResult
            ->expects($this->any())
            ->method('getLineRate')
            ->willReturn(0);

        $scope = $this->getMockBuilder(
            \Magento\Framework\App\ScopeInterface::class
        )->disableOriginalConstructor()->getMock();
        
        $itemMock = $this->getMockBuilder(QuoteItem::class)->disableOriginalConstructor()->getMock();
        
        $itemMock
            ->expects($this->any())
            ->method('getSku')
            ->willReturn('abc');
        
        $itemMock->expects($this->any())
        ->method('setData')
        ->willReturn($itemMock);
        $itemMock->expects($this->any())
        ->method('save')
        ->willReturn($itemMock);

        $visibleItems = [
            11 => $itemMock,
        ];
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($visibleItems); 

        $this->testObject->calculateTaxDetails(
            $taxQuoteDetails,
            $getTaxResult,
            false,
            $scope
        );
        
    }

    /**
     * tests calculateTotalQuantities
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTotalQuantities
     */
    public function testCalculateTotalQuantities()
    {
        $item1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();

        $extensionAttr1 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr1
            ->expects($this->any())
            ->method('getTotalQuantity')
            ->willReturn(1);

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
        $item1
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr1);
            
        $extensionAttr2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr2
            ->expects($this->any())
            ->method('getTotalQuantity')
            ->willReturn(1);

        $item2 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
        )->disableOriginalConstructor()->getMock();
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
        $item2
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr2);

        $extensionAttr3 = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface::class
        )->disableOriginalConstructor()->getMock();
        $extensionAttr3
            ->expects($this->any())
            ->method('getTotalQuantity')
            ->willReturn(1);

        $item3 = $this->getMockBuilder(
                \Magento\Tax\Api\Data\QuoteDetailsItemInterface::class
            )->disableOriginalConstructor()->getMock();
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
        $item3
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttr3);

        $this->testObject->calculateTotalQuantities([$item1, $item2, $item3]);
    }
}
