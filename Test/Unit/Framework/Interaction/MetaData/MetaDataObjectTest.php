<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\MetaData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject
 */
class MetaDataObjectTest extends TestCase
{
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
     * Mock arrayTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ArrayType|PHPUnit\Framework\MockObject\MockObject
     */
    private $arrayTypeFactoryInstance;

    /**
     * Mock arrayTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ArrayTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $arrayTypeFactory;

    /**
     * Mock booleanTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\BooleanType|PHPUnit\Framework\MockObject\MockObject
     */
    private $booleanTypeFactoryInstance;

    /**
     * Mock booleanTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\BooleanTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $booleanTypeFactory;

    /**
     * Mock doubleTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DoubleType|PHPUnit\Framework\MockObject\MockObject
     */
    private $doubleTypeFactoryInstance;

    /**
     * Mock doubleTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DoubleTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $doubleTypeFactory;

    /**
     * Mock integerTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\IntegerType|PHPUnit\Framework\MockObject\MockObject
     */
    private $integerTypeFactoryInstance;

    /**
     * Mock integerTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\IntegerTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $integerTypeFactory;

    /**
     * Mock dataObjectTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectType|PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectTypeFactoryInstance;

    /**
     * Mock dataObjectTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectTypeFactory;

    /**
     * Mock stringTypeFactoryInstance
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\StringType|PHPUnit\Framework\MockObject\MockObject
     */
    private $stringTypeFactoryInstance;

    /**
     * Mock stringTypeFactory
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\StringTypeFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $stringTypeFactory;

    /**
     * Mock metaDataProperties
     *
     * @var \array|PHPUnit\Framework\MockObject\MockObject
     */
    private $metaDataProperties;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->metaDataObjectFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject::class);
        $this->metaDataObjectFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory::class);
        $this->metaDataObjectFactory->method('create')->willReturn($this->metaDataObjectFactoryInstance);
        $this->arrayTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\ArrayType::class);
        $this->arrayTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\ArrayTypeFactory::class);
        $this->arrayTypeFactory->method('create')->willReturn($this->arrayTypeFactoryInstance);
        $this->booleanTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\BooleanType::class);
        $this->booleanTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\BooleanTypeFactory::class);
        $this->booleanTypeFactory->method('create')->willReturn($this->booleanTypeFactoryInstance);
        $this->doubleTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\DoubleType::class);
        $this->doubleTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\DoubleTypeFactory::class);
        $this->doubleTypeFactory->method('create')->willReturn($this->doubleTypeFactoryInstance);
        $this->integerTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\IntegerType::class);
        $this->integerTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\IntegerTypeFactory::class);
        $this->integerTypeFactory->method('create')->willReturn($this->integerTypeFactoryInstance);
        $this->dataObjectTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectType::class);
        $this->dataObjectTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectTypeFactory::class);
        $this->dataObjectTypeFactory->method('create')->willReturn($this->dataObjectTypeFactoryInstance);
        $this->stringTypeFactoryInstance = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\StringType::class);
        $this->stringTypeFactory = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\MetaData\StringTypeFactory::class);
        $this->stringTypeFactory->method('create')->willReturn($this->stringTypeFactoryInstance);
        $this->metaDataProperties = [];
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject::class,
            [
                'metaDataObjectFactory' => $this->metaDataObjectFactory,
                'arrayTypeFactory' => $this->arrayTypeFactory,
                'booleanTypeFactory' => $this->booleanTypeFactory,
                'doubleTypeFactory' => $this->doubleTypeFactory,
                'integerTypeFactory' => $this->integerTypeFactory,
                'dataObjectTypeFactory' => $this->dataObjectTypeFactory,
                'stringTypeFactory' => $this->stringTypeFactory,
                'metaDataProperties' => $this->metaDataProperties,
            ]
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestValidateData()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestValidateData
     */
    public function testValidateData(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCacheKey()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetCacheKey
     */
    public function testGetCacheKey(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetCacheKeyFromObject()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetCacheKeyFromObject
     */
    public function testGetCacheKeyFromObject(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }
}
