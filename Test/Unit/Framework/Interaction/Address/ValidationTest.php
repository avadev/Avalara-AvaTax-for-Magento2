<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Address;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvalaraConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Api\RestAddressInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;

/**
 * Class ValidationTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Address
 */
class ValidationTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default"; 
    const XML_PATH_AVATAX_MODULE_ENABLED = "tax/avatax/enabled";
    
    protected $objectManagerHelper;
    protected $context;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->interactionAddress = $this->createMock(Address::class);

        $this->addressService = $this->createMock(RestAddressInterface::class);

        $this->dataObjectFactory = $this->getMockBuilder(DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['hasData','getData'])
            ->setMethods(['create'])
            ->getMock();;

        $this->restConfig = $this->createMock(RestConfig::class);
    
        $appName = 'Avalara';
        $appVersion = '2.3.1';
        $environment = 'sandbox';
        $machineName = '';
        $type = 'tax';
        $guzzleParams = [];
        $this->ValidationObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::class,
            [
                'interactionAddress' => $this->interactionAddress,
                'addressService' => $this->addressService,
                'dataObjectFactory' => $this->dataObjectFactory,
                'restConfig' => $this->restConfig
            ]
        );
        parent::setUp();
    }    
    
    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::validateAddress
     */
    public function testWithSecurity()
    {

        $this->addressInterface = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getCounty','getExtensionAttributes','setCounty'])
            ->getMockForAbstractClass();

        $this->result = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result::class);

        $data = new \Magento\Framework\DataObject();
        $data->setRegion('Alaska');

        $this->interactionAddress
             ->method('getAddress')
             ->with($this->addressInterface)
             ->willReturn($data);

        $this->restConfig
             ->method('getTextCaseMixed')
             ->willReturn('Test');

        $this->dataObjectFactory
             ->method('hasData')
             ->with('validated_addresses')
             ->willReturn([]);

        $this->dataObjectFactory
             ->method('create')
             ->with(['data' => ['address' => $data, 'text_case' => 'Test']])
             ->willReturn($this->dataObjectFactory);

        $this->addressService
             ->method('validate')
             ->with($this->dataObjectFactory, null, 1)
             ->willReturn($this->result);    

        $this->ValidationObject->validateAddress($this->addressInterface, 1);
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::validateAddress
     */
    public function testWithSecurityAddress()
    {

        $this->addressInterface = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getCounty','getExtensionAttributes','setCounty'])
            ->getMockForAbstractClass();

        $this->result = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result::class);

        $result['county'] = "Test";
        $resultObj = new \Magento\Framework\DataObject();
        $resultObj->setData($result);
        $data = new \Magento\Framework\DataObject();
        $data->setData(['validated_addresses' => [$resultObj]]);

        $this->interactionAddress
             ->method('getAddress')
             ->with($this->addressInterface)
             ->willReturn($data);

        $this->restConfig
             ->method('getTextCaseMixed')
             ->willReturn('Test');

        $this->dataObjectFactory
             ->method('getData')
             ->with('validated_addresses')
             ->willReturn($data);

        $this->dataObjectFactory
             ->method('create')
             ->with(['data' => ['address' => $data, 'text_case' => 'Test']])
             ->willReturn($this->dataObjectFactory);

        $this->addressService
             ->method('validate')
             ->with($this->dataObjectFactory, null, 1)
             ->willReturn($data);    

        $this->ValidationObject->validateAddress($this->addressInterface, 1);
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::validateAddress
     */
    public function testWithSecurityAddressInterface()
    {

        $this->addressInterface = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->setMethods(['getCounty','getExtensionAttributes','setCounty'])
            ->getMockForAbstractClass();

        $this->result = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result::class);

        $result[0]['county'] = "Test";
        $resultObj = new \Magento\Framework\DataObject();
        $resultObj->setData($result);
        $data = new \Magento\Framework\DataObject();
        $data->setData(['validated_addresses' => [$resultObj]]);

        $this->interactionAddress
             ->method('getAddress')
             ->with($this->addressInterface)
             ->willReturn($data);

        $this->addressInterface
             ->method('getExtensionAttributes')
             ->willReturn($resultObj);

        $this->interactionAddress
             ->method('convertAvaTaxValidAddressToCustomerAddress')
             ->with($resultObj , $this->addressInterface)
             ->willReturn($this->addressInterface);

        $this->restConfig
             ->method('getTextCaseMixed')
             ->willReturn('Test');

        $this->dataObjectFactory
             ->method('getData')
             ->with('validated_addresses')
             ->willReturn($data);

        $this->dataObjectFactory
             ->method('create')
             ->with(['data' => ['address' => $data, 'text_case' => 'Test']])
             ->willReturn($this->dataObjectFactory);

        $this->addressService
             ->method('validate')
             ->with($this->dataObjectFactory, null, 1)
             ->willReturn($data);    

        $this->ValidationObject->validateAddress($this->addressInterface, 1);
    }

    /**
     * tests validateAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation::validateAddress
     */
    public function testWithSecurityAddressDefault()
    {

        $this->addressInterface = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->addMethods(['getCounty','getExtensionAttributes','setCounty'])
            ->getMockForAbstractClass();

        $this->result = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result::class);

        $result[0]['county'] = "Test";
        $resultObj = new \Magento\Framework\DataObject();
        $resultObj->setData($result);
        $data = new \Magento\Framework\DataObject();
        $data->setData(['validated_addresses' => [$resultObj]]);

        $this->interactionAddress
             ->method('getAddress')
             ->with($this->addressInterface)
             ->willReturn($data);

        $this->addressInterface
             ->method('getExtensionAttributes')
             ->willReturn($resultObj);

        $this->interactionAddress
             ->method('convertAvaTaxValidAddressToCustomerAddress')
             ->with($resultObj , $this->addressInterface)
             ->willReturn($this->addressInterface);

        $this->restConfig
             ->method('getTextCaseMixed')
             ->willReturn('Test');

        $this->dataObjectFactory
             ->method('getData')
             ->with('validated_addresses')
             ->willReturn($data);

        $this->dataObjectFactory
             ->method('create')
             ->with(['data' => ['address' => $data, 'text_case' => 'Test']])
             ->willReturn($this->dataObjectFactory);

        $this->addressService
             ->method('validate')
             ->with($this->dataObjectFactory, null, 1)
             ->willReturn($data);    

        $this->expectException(LocalizedException::class);
        $this->ValidationObject->validateAddress($this->addressInterface, 1);
    }
}