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
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class CustomerTest
 * @covers \ClassyLlama\AvaTax\Helper\Customer
 * @package ClassyLlama\AvaTax\Helper
 */
class CustomerTest extends TestCase
{
    /**
     * Mock customerRepository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Mock context
     *
     * @var \Magento\Framework\App\Helper\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Helper\Customer
     */
    private $testObject;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\Customer::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Helper\Customer::class,
            [
                'customerRepository' => $this->customerRepository,
                'config' => $this->config,
                'context' => $this->context,
            ]
        );
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerAttributeValue
     */
    public function testGetCustomerAttributeValue()
    {
        $customerCode = "abcde";
        $this->attribute = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer
            ->expects($this->any())
            ->method('getCustomAttribute')
            ->with($customerCode)
            ->willReturn($this->attribute);
        $this->testObject->getCustomerAttributeValue($this->customer, $customerCode);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerAttributeValue
     */
    public function testGetCustomerAttributeValueWhenAttributeNull()
    {
        $customerCode = "abcde";
        $this->attribute = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer
            ->expects($this->any())
            ->method('getCustomAttribute')
            ->with($customerCode)
            ->willReturn(null);
        $this->testObject->getCustomerAttributeValue($this->customer, $customerCode);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerAttributeValue
     */
    public function testGetCustomerAttributeValueWhenMethodExists()
    {
        $customerCode = "first_name";
        $this->attribute = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer
            ->expects($this->any())
            ->method('getCustomAttribute')
            ->with($customerCode)
            ->willReturn($this->attribute);
        $this->testObject->getCustomerAttributeValue($this->customer, $customerCode);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::generateCustomerCodeFromNameId
     */
    public function testGenerateCustomerCodeFromNameIdWhenCustomerNull()
    {
        $name = \ClassyLlama\AvaTax\Helper\Config::CUSTOMER_MISSING_NAME;
        $id = \ClassyLlama\AvaTax\Helper\Config::CUSTOMER_GUEST_ID;
        $this->testObject->generateCustomerCodeFromNameId();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::generateCustomerCodeFromNameId
     */
    public function testGenerateCustomerCodeFromNameId()
    {
        $this->customer
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->customer
            ->expects($this->any())
            ->method('getFirstname')
            ->willReturn("abcdef");
        $this->customer
            ->expects($this->any())
            ->method('getLastname')
            ->willReturn("text");
        $this->testObject->generateCustomerCodeFromNameId($this->customer);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::generateCustomerCodeFromAttribute
     */
    public function testGenerateCustomerCodeFromAttributeWhenEmail()
    {
        $attribute = 'email';
        $this->testObject->generateCustomerCodeFromAttribute($this->customer, $attribute);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::generateCustomerCodeFromAttribute
     */
    public function testGenerateCustomerCodeFromAttributeWhenFirstName()
    {
        $attribute = 'first_name';
        $this->customer
            ->expects($this->any())
            ->method('getFirstname')
            ->willReturn("abcdef");
        $this->testObject->generateCustomerCodeFromAttribute($this->customer, $attribute);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::generateCustomerCodeFromAttribute
     */
    public function testGenerateCustomerCodeFromAttribute()
    {
        $attribute = '';
        $this->testObject->generateCustomerCodeFromAttribute($this->customer, $attribute);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerCodeByCustomerId
     */
    public function testGetCustomerCodeByCustomerIdWhenCustomerCodeFormatIsId()
    {
        $customerId = 1;
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customer);
        $storeId = 0;
        $this->config
            ->expects($this->any())
            ->method('getCustomerCodeFormat')
            ->with($storeId)
            ->willReturn('id');
        $this->testObject->getCustomerCodeByCustomerId($customerId);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerCodeByCustomerId
     */
    public function testGetCustomerCodeByCustomerIdWhenCustomerCodeFormatIsNameId()
    {
        $customerId = 1;
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customer);
        $storeId = 0;
        $this->config
            ->expects($this->any())
            ->method('getCustomerCodeFormat')
            ->with($storeId)
            ->willReturn('name_id');
        $this->testObject->getCustomerCodeByCustomerId($customerId);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerCodeByCustomerId
     */
    public function testGetCustomerCodeByCustomerId()
    {
        $customerId = 1;
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->customer
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->testObject->getCustomerCodeByCustomerId($customerId);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerCodeByCustomerId
     */
    public function testGetCustomerCodeByCustomerIdWhenNoSuchEntityException()
    {
        $customerId = 1;
        $e = $this->objectManager->getObject(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($e);
        $this->testObject->getCustomerCodeByCustomerId($customerId);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Customer::getCustomerCodeByCustomerId
     */
    public function testGetCustomerCodeByCustomerIdWhenLocalizedException()
    {
        $customerId = 1;
        $e = $this->objectManager->getObject(\Magento\Framework\Exception\LocalizedException::class);
        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($e);
        $this->testObject->getCustomerCodeByCustomerId($customerId);
    }
}
