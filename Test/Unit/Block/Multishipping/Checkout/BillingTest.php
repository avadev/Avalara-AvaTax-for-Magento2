<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\Multishipping\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing
 */
class BillingTest extends TestCase
{
    /**
     * Mock context
     *
     * @var \Magento\Framework\View\Element\Template\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Mock paymentHelper
     *
     * @var \Magento\Payment\Helper\Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentHelper;

    /**
     * Mock methodSpecificationFactoryInstance
     *
     * @var \Magento\Payment\Model\Checks\Specification|PHPUnit_Framework_MockObject_MockObject
     */
    private $methodSpecificationFactoryInstance;

    /**
     * Mock methodSpecificationFactory
     *
     * @var \Magento\Payment\Model\Checks\SpecificationFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $methodSpecificationFactory;

    /**
     * Mock multishipping
     *
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping|PHPUnit_Framework_MockObject_MockObject
     */
    private $multishipping;

    /**
     * Mock checkoutSession
     *
     * @var \Magento\Checkout\Model\Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSession;

    /**
     * Mock paymentSpecification
     *
     * @var \Magento\Payment\Model\Method\SpecificationInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentSpecification;

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
     * @var \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing
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

        $this->paymentHelper = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->methodSpecificationFactoryInstance = $this->getMockBuilder(\Magento\Payment\Model\Checks\Specification::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->methodSpecificationFactory = $this->getMockBuilder(\Magento\Payment\Model\Checks\SpecificationFactory::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->methodSpecificationFactory->method('create')->willReturn($this->methodSpecificationFactoryInstance);
        $this->multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->paymentSpecification = $this->getMockBuilder(\Magento\Payment\Model\Method\SpecificationInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->addressValidation = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing::class,
            [
                'context' => $this->context,
                'paymentHelper' => $this->paymentHelper,
                'methodSpecificationFactory' => $this->methodSpecificationFactory,
                'multishipping' => $this->multishipping,
                'checkoutSession' => $this->checkoutSession,
                'paymentSpecification' => $this->paymentSpecification,
                'addressValidation' => $this->addressValidation,
            ]
        );
    }

    /**
     * tests isValidationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing::isValidationEnabled
     */
    public function testIsValidationEnabled()
    {
        $this->testObject->isValidationEnabled();
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing::validateAddress
     */
    public function testValidateAddress()
    {
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->testObject->validateAddress($address);
    }

    /**
     * tests getStoreCode
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Multishipping\Checkout\Billing::getStoreCode
     */
    public function testGetStoreCode()
    {
        $this->assertIsString($this->testObject->getStoreCode());
    }
}
