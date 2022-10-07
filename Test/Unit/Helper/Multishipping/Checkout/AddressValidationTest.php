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
namespace ClassyLlama\AvaTax\Test\Unit\Helper\Multishipping\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AddressValidationTest
 * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation
 * @package ClassyLlama\AvaTax\Test\Unit\Helper\Multishipping\Checkout
 */
class AddressValidationTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    const XML_PATH_AVATAX_ADDRESS_VALIDATION_ENABLED = "tax/avatax/address_validation_enabled";
    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Mock serializer
     *
     * @var \Magento\Framework\Serialize\SerializerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validation = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressBlock = $this->getMockBuilder(\ClassyLlama\AvaTax\Block\CustomerAddress::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class,
            [
                'config' => $this->config,
                'storeManager' => $this->storeManager,
                'validation' => $this->validation,
                'customerAddressBlock' => $this->customerAddressBlock,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * tests isValidationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::isValidationEnabled
     */
    public function testIsValidationEnabled()
    {
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_AVATAX_ADDRESS_VALIDATION_ENABLED,
            )
            ->willReturn(1);
        $this->assertEquals($this->config->isAddressValidationEnabled(self::SCOPE_STORE, $storecode),$this->testObject->isValidationEnabled());

    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::validateAddress
     */
    public function testValidateAddress()
    {
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreetLine',
                    'getCity',
                    'getTelephone',
                    'getFax',
                    'getCompany',
                    'getPostcode',
                    'getCountryId',
                ]
            )->getMock();
        $data = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'street' => "Street 1\nStreet 2",
            'city' => 'Odessa',
            'telephone' => '555-55-55',
            'country_id' => 1,
            'postcode' => 07201,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $addressMock->method('getFirstName')->willReturn($data['firstname']);
        $addressMock->method('getLastname')->willReturn($data['lastname']);
        $addressMock->method('getStreetLine')->with(1)->willReturn($data['street']);
        $addressMock->method('getCity')->willReturn($data['city']);
        $addressMock->method('getTelephone')->willReturn($data['telephone']);
        $addressMock->method('getFax')->willReturn($data['fax']);
        $addressMock->method('getCompany')->willReturn($data['company']);
        $addressMock->method('getPostcode')->willReturn($data['postcode']);
        $addressMock->method('getCountryId')->willReturn($data['country_id']);

        
        $this->customerAddressBlock
            ->expects($this->any())
            ->method('getCountriesEnabled')
            ->willReturn("1,2,3");
        $this->customerAddressBlock
            ->expects($this->any())
            ->method('getInstructions')
            ->willReturn('{"name":"John", "age":30, "car":null}');
        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())->method('getId')->willReturn(1);


        $suggestedAddMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreetLine',
                    'getCity',
                    'getTelephone',
                    'getFax',
                    'getCompany',
                    'getPostcode',
                    'getCountryId',
                ]
            )->getMock();
        $data = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'street' => "street test",
            'city' => 'Odessa',
            'telephone' => '555-55-55',
            'country_id' => 1,
            'postcode' => 123456,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $suggestedAddMock->method('getFirstName')->willReturn($data['firstname']);
        $suggestedAddMock->method('getLastname')->willReturn($data['lastname']);
        $suggestedAddMock->method('getStreetLine')->with(1)->willReturn("abc");
        $suggestedAddMock->method('getCity')->willReturn($data['city']);
        $suggestedAddMock->method('getTelephone')->willReturn($data['telephone']);
        $suggestedAddMock->method('getFax')->willReturn($data['fax']);
        $suggestedAddMock->method('getCompany')->willReturn($data['company']);
        $suggestedAddMock->method('getPostcode')->willReturn("1234");
        $suggestedAddMock->method('getCountryId')->willReturn($data['country_id']);

        $this->validation
            ->expects($this->any())
            ->method('validateAddress')
            ->with(
                $addressMock,
                1
            )
            ->willReturn($suggestedAddMock);
        $this->assertIsArray($this->testObject->validateAddress($addressMock));
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::validateAddress
     */
    public function testValidateAddressWhenExceptionThrown()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreetLine',
                    'getCity',
                    'getTelephone',
                    'getFax',
                    'getCompany',
                    'getPostcode',
                    'getCountryId',
                ]
            )->getMock();
        $data = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'street' => "Street 1\nStreet 2",
            'city' => 'Odessa',
            'telephone' => '555-55-55',
            'country_id' => 1,
            'postcode' => 07201,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $addressMock->method('getFirstName')->willReturn($data['firstname']);
        $addressMock->method('getLastname')->willReturn($data['lastname']);
        $addressMock->method('getStreetLine')->with(1)->willReturn($data['street']);
        $addressMock->method('getCity')->willReturn($data['city']);
        $addressMock->method('getTelephone')->willReturn($data['telephone']);
        $addressMock->method('getFax')->willReturn($data['fax']);
        $addressMock->method('getCompany')->willReturn($data['company']);
        $addressMock->method('getPostcode')->willReturn($data['postcode']);
        $addressMock->method('getCountryId')->willReturn($data['country_id']);
        $this->customerAddressBlock
            ->expects($this->any())
            ->method('getCountriesEnabled')
            ->willReturn("1,2,3");
        $this->customerAddressBlock
            ->expects($this->any())
            ->method('getInstructions')
            ->willReturn('{"name":"John", "age":30, "car":null}');
        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())->method('getId')->willReturn(1);
        $this->validation
            ->expects($this->any())
            ->method('validateAddress')
            ->with(
                $addressMock,
                1
            )
            ->willThrowException($exception);
        $this->assertIsArray($this->testObject->validateAddress($addressMock));
    }

    /**
     * tests compareFields
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::compareFields
     */
    public function testCompareFields()
    {
        $mockedInstance = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class)
        ->disableOriginalConstructor()
        ->getMock();
        $reflectedMethod = new \ReflectionMethod(
            \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class,
            'compareFields'
        );
        $reflectedMethod->setAccessible(true);

        //create original address mock
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','getPostcode'])
            ->getMock();
        
        $addressMock->method('getPostcode')->willReturn(12345);
        $addressMock
        ->expects($this->any())
        ->method('getData')
        ->willReturn($addressMock);
        
        //create valid address mock
        $addressMockExpected = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','getPostcode'])
            ->getMock();
        
        $addressMockExpected->method('getPostcode')->willReturn('67890');
        $addressMockExpected
        ->expects($this->any())
        ->method('getData')
        ->willReturn($addressMockExpected);
                
        $reflectedMethod->invoke($mockedInstance, $addressMock, $addressMockExpected);
    }

    /**
     * tests prepareAddressString
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::prepareAddressString
     */
    public function testPrepareAddressString()
    {
        $mockedInstance = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class)
        ->disableOriginalConstructor()
        ->getMock();
        $reflectedMethod = new \ReflectionMethod(
            \ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation::class,
            'prepareAddressString'
        );
        $reflectedMethod->setAccessible(true);

        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreetLine',
                    'getCity',
                    'getTelephone',
                    'getFax',
                    'getCompany',
                    'getPostcode',
                    'getCountryId',
                ]
            )->getMock();
        $data = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'street' => "Street 1\nStreet 2",
            'city' => 'Odessa',
            'telephone' => '555-55-55',
            'country_id' => 1,
            'postcode' => 07201,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $addressMock->method('getFirstName')->willReturn($data['firstname']);
        $addressMock->method('getLastname')->willReturn($data['lastname']);
        $addressMock->method('getStreetLine')->with(1)->willReturn($data['street']);
        $addressMock->method('getCity')->willReturn($data['city']);
        $addressMock->method('getTelephone')->willReturn($data['telephone']);
        $addressMock->method('getFax')->willReturn($data['fax']);
        $addressMock->method('getCompany')->willReturn($data['company']);
        $addressMock->method('getPostcode')->willReturn($data['postcode']);
        $addressMock->method('getCountryId')->willReturn($data['country_id']);

        $changeFieldsArr = ['postcode'];

        $reflectedMethod->invoke($mockedInstance, $addressMock, $changeFieldsArr);
        
    }
}
