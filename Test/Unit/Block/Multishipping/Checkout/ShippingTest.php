<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\Multishipping\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping
 */
class ShippingTest extends TestCase
{
    /**
     * Mock context
     *
     * @var \Magento\Framework\View\Element\Template\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Mock filterGridFactoryInstance
     *
     * @var \Magento\Framework\Filter\DataObject\Grid|PHPUnit_Framework_MockObject_MockObject
     */
    private $filterGridFactoryInstance;

    /**
     * Mock filterGridFactory
     *
     * @var \Magento\Framework\Filter\DataObject\GridFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $filterGridFactory;

    /**
     * Mock multishipping
     *
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping|PHPUnit_Framework_MockObject_MockObject
     */
    private $multishipping;

    /**
     * Mock taxHelper
     *
     * @var \Magento\Tax\Helper\Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $taxHelper;

    /**
     * Mock priceCurrency
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * Mock addressValidation
     *
     * @var \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation|PHPUnit_Framework_MockObject_MockObject
     */
    private $addressValidation;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);        

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->storeMock->expects($this->any())
                                ->method('getCode')
                                ->willReturn('US');
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->storeManagerMock->expects($this->any())
                                ->method('getStore')
                                ->willReturn($this->storeMock);
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->context->expects($this->any())
                                ->method('getStoreManager')
                                ->willReturn($this->storeManagerMock);
        $this->filterGridFactoryInstance = $this->getMockBuilder(\Magento\Framework\Filter\DataObject\Grid::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->filterGridFactory = $this->getMockBuilder(\Magento\Framework\Filter\DataObject\GridFactory::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->filterGridFactory->method('create')->willReturn($this->filterGridFactoryInstance);
        $this->multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->taxHelper = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->addressValidation = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping::class,
            [
                'context' => $this->context,
                'filterGridFactory' => $this->filterGridFactory,
                'multishipping' => $this->multishipping,
                'taxHelper' => $this->taxHelper,
                'priceCurrency' => $this->priceCurrency,
                'addressValidation' => $this->addressValidation,
            ]
        );
    }

    /**
     * tests isValidationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping::isValidationEnabled
     */
    public function testIsValidationEnabled()
    {
        $this->testObject->isValidationEnabled();
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping::validateAddress
     */
    public function testValidateAddress()
    {
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $address->expects($this->any())
                        ->method('getId')
                        ->willReturn(1);
        $address->expects($this->any())
                        ->method('getCountryId')
                        ->willReturn("US");
        $this->testObject->validateAddress($address);
    }

    /**
     * tests getStoreCode
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Shipping::getStoreCode
     */
    public function testGetStoreCode()
    {
        $this->assertIsString($this->testObject->getStoreCode());
    }
}
