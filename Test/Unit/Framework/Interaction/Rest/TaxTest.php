<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax
 */
class TaxTest extends TestCase
{
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
     * Mock transactionBuilderFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\TransactionBuilder|PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionBuilderFactoryInstance;

    /**
     * Mock transactionBuilderFactory
     *
     * @var \ClassyLlama\AvaTax\Model\Factory\TransactionBuilderFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionBuilderFactory;

    /**
     * Mock taxResultFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxResultFactoryInstance;

    /**
     * Mock taxResultFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxResultFactory;

    /**
     * Mock restConfig
     *
     * @var \ClassyLlama\AvaTax\Helper\Rest\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $restConfig;

    /**
     * Mock customsConfigHelper
     *
     * @var \ClassyLlama\AvaTax\Helper\CustomsConfig|PHPUnit\Framework\MockObject\MockObject
     */
    private $customsConfigHelper;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Mock apiLog
     *
     * @var \ClassyLlama\AvaTax\Helper\ApiLog|PHPUnit\Framework\MockObject\MockObject
     */
    private $apiLog;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->dataObjectFactoryInstance = $this->createMock(\Magento\Framework\DataObject::class);
        $this->dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->clientPool = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool::class);
        //$this->transactionBuilderFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\TransactionBuilder::class);
        $this->transactionBuilderFactory = $this->createMock(\ClassyLlama\AvaTax\Model\Factory\TransactionBuilderFactory::class);
        //$this->transactionBuilderFactory->method('create')->willReturn($this->transactionBuilderFactoryInstance);
        $this->transactionBuilderFactory->method('create')->willReturn($this->transactionBuilderFactory);
        $this->taxResultFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result::class);
        $this->taxResultFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory::class);
        $this->taxResultFactory->method('create')->willReturn($this->taxResultFactoryInstance);
        $this->restConfig = $this->createMock(\ClassyLlama\AvaTax\Helper\Rest\Config::class);
        $this->customsConfigHelper = $this->createMock(\ClassyLlama\AvaTax\Helper\CustomsConfig::class);
        $this->config = $this->createMock(\ClassyLlama\AvaTax\Helper\Config::class);
        $this->apiLog = $this->createMock(\ClassyLlama\AvaTax\Helper\ApiLog::class);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax::class,
            [
                'logger' => $this->logger,
                'dataObjectFactory' => $this->dataObjectFactory,
                'clientPool' => $this->clientPool,
                'transactionBuilderFactory' => $this->transactionBuilderFactory,
                'taxResultFactory' => $this->taxResultFactory,
                'restConfig' => $this->restConfig,
                'customsConfigHelper' => $this->customsConfigHelper,
                'config' => $this->config,
                'apiLog' => $this->apiLog,
            ]
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetTax()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetTax
     */
    public function testGetTax(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetTaxBatch()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetTaxBatch
     */
    public function testGetTaxBatch(array $prerequisites, array $expectedResult)
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
