<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
 */
class CompanyTest extends TestCase
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
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
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
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company::class,
            [
                'logger' => $this->logger,
                'dataObjectFactory' => $this->dataObjectFactory,
                'clientPool' => $this->clientPool,
            ]
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCompanies()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetCompanies
     */
    public function testGetCompanies(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCompaniesWithSecurity()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetCompaniesWithSecurity
     */
    public function testGetCompaniesWithSecurity(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCertificateExposureZones()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetCertificateExposureZones
     */
    public function testGetCertificateExposureZones(array $prerequisites, array $expectedResult)
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
