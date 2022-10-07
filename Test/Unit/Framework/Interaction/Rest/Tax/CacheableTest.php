<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest\Tax;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Cacheable
 */
class CacheableTest extends TestCase
{
    /**
     * Mock phpSerialize
     *
     * @var \Zend\Serializer\Adapter\PhpSerialize|PHPUnit\Framework\MockObject\MockObject
     */
    private $phpSerialize;

    /**
     * Mock cache
     *
     * @var \Magento\Framework\App\CacheInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $cache;

    /**
     * Mock avaTaxLogger
     *
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger|PHPUnit\Framework\MockObject\MockObject
     */
    private $avaTaxLogger;

    /**
     * Mock taxInteraction
     *
     * @var \ClassyLlama\AvaTax\Api\RestTaxInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxInteraction;

    /**
     * Mock metaDataObjectFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject|PHPUnit\Framework\MockObject\MockObject
     */
    private $metaDataObjectFactoryInstance;

    /**
     * Mock metaDataObjectFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $metaDataObjectFactory;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Cacheable
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->phpSerialize = $this->createMock(\Zend\Serializer\Adapter\PhpSerialize::class);
        $this->cache = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->avaTaxLogger = $this->createMock(\ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger::class);
        $this->taxInteraction = $this->createMock(\ClassyLlama\AvaTax\Api\RestTaxInterface::class);
        $this->metaDataObjectFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject::class);
        $this->metaDataObjectFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory::class);
        $this->metaDataObjectFactory->method('create')->willReturn($this->metaDataObjectFactoryInstance);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Cacheable::class,
            [
                'phpSerialize' => $this->phpSerialize,
                'cache' => $this->cache,
                'avaTaxLogger' => $this->avaTaxLogger,
                'taxInteraction' => $this->taxInteraction,
                'metaDataObjectFactory' => $this->metaDataObjectFactory,
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

    /**
     * @return array
     */
    public function dataProviderForTest__call()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTest__call
     */
    public function test__call(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }
}
