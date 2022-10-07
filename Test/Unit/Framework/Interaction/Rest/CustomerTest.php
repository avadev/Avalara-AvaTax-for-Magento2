<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Customer
 */
class CustomerTest extends TestCase
{
    /**
     * Mock customerHelper
     *
     * @var \ClassyLlama\AvaTax\Helper\Customer|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerHelper;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Mock logger
     *
     * @var \Psr\Log\LoggerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

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
     * Mock clientPool
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool|PHPUnit\Framework\MockObject\MockObject
     */
    private $clientPool;

    /**
     * Mock customerModelFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\CustomerModel|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerModelFactoryInstance;

    /**
     * Mock customerModelFactory
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerModelFactory;

    /**
     * Mock addressRepository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $addressRepository;

    /**
     * Mock customersModelFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\LinkCustomersModel|PHPUnit\Framework\MockObject\MockObject
     */
    private $customersModelFactoryInstance;

    /**
     * Mock customersModelFactory
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\LinkCustomersModelFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $customersModelFactory;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Customer
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerHelper = $this->createMock(\ClassyLlama\AvaTax\Helper\Customer::class);
        $this->config = $this->createMock(\ClassyLlama\AvaTax\Helper\Config::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->dataObjectFactoryInstance = $this->createMock(\Magento\Framework\DataObject::class);
        $this->dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->clientPool = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool::class);
        //$this->customerModelFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\CustomerModel::class);
        $this->customerModelFactory = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory::class);
        $this->customerModelFactory->method('create')->willReturn($this->customerModelFactory);
        $this->addressRepository = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        //$this->customersModelFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\LinkCustomersModel::class);
        $this->customersModelFactory = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\LinkCustomersModelFactory::class);
        //$this->customersModelFactory->method('create')->willReturn($this->customersModelFactoryInstance);
        $this->customersModelFactory->method('create')->willReturn($this->customersModelFactory);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Customer::class,
            [
                'customerHelper' => $this->customerHelper,
                'config' => $this->config,
                'logger' => $this->logger,
                'dataObjectFactory' => $this->dataObjectFactory,
                'clientPool' => $this->clientPool,
                'customerModelFactory' => $this->customerModelFactory,
                'addressRepository' => $this->addressRepository,
                'customersModelFactory' => $this->customersModelFactory,
            ]
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCertificatesList()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForTestDownloadCertificate()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestDownloadCertificate
     */
    public function testDownloadCertificate(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestDeleteCertificate()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestDeleteCertificate
     */
    public function testDeleteCertificate(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestUpdateCustomer()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestUpdateCustomer
     */
    public function testUpdateCustomer(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestCreateCustomer()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestCreateCustomer
     */
    public function testCreateCustomer(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestSendCertExpressInvite()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestSendCertExpressInvite
     */
    public function testSendCertExpressInvite(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetClient()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetClient
     */
    public function testGetClient(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestPing()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestPing
     */
    public function testPing(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }
}
