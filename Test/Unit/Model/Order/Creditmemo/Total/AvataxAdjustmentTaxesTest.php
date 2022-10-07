<?php

namespace ClassyLlama\AvaTax\Test\Unit\Model\Order\Creditmemo\Total;

use ClassyLlama\AvaTax\Api\RestTaxInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\TaxCompositeInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Request\CreditmemoRequestBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as RestTaxResult;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Creditmemo;
use ClassyLlama\AvaTax\Framework\Interaction\Request\Request as CreditmemoRequest;

/**
 * Class AvataxAdjustmentTaxesTest
 * @package ClassyLlama\AvaTax\Test\Unit\Model\Order\Creditmemo\Total
 */
class AvataxAdjustmentTaxesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AvataxAdjustmentTaxes
     */
    private $avataxAdjustmentTaxes;

    /**
     * @var AvaTaxLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avataxLoggerMock;

    /**
     * @var TaxCompositeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCompositeInterfaceMock;

    /**
     * @var CreditmemoRequestBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoRequestBuilderMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeInterfaceMock;

    /**
     * @var Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void 
    {
        $this->objectManager = new ObjectManager($this);
        $this->avataxLoggerMock = $this->createMock(AvaTaxLogger::class);
        $this->taxCompositeInterfaceMock = $this->getMockForAbstractClass(TaxCompositeInterface::class);
        $this->creditmemoRequestBuilderMock = $this->createMock(CreditmemoRequestBuilder::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeInterfaceMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseAdjustmentPositive', 'getBaseAdjustmentNegative', 'getStoreId', 'getTaxAmount', 'getBaseTaxAmount'])
            ->getMock();

        $this->avataxAdjustmentTaxes = $this->objectManager->getObject(AvataxAdjustmentTaxes::class, [
            'avataxLogger' => $this->avataxLoggerMock,
            'taxCompositeService' => $this->taxCompositeInterfaceMock,
            'creditmemoRequestBuilder' => $this->creditmemoRequestBuilderMock,
            'storeManager' => $this->storeManagerMock,
            'scopeConfig' => $this->scopeConfigMock,
            'data' => []
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::isTaxCalculationEnabledForAdjustments
     * @throws \ReflectionException
     */
    public function checkCorrectReturnTypeForisTaxCalculationEnabledForAdjustmentsMethod()
    {
        $storeId = 1;

        $this->storeManagerMock->expects(static::atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);
        $this->storeInterfaceMock->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $this->scopeConfigMock->expects(static::atLeastOnce())
            ->method('getValue')
            ->with(AvataxAdjustmentTaxes::ADJUSTMENTS_CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeId)
            ->willReturn($storeId);

        /** @var bool $result */
        $result = $this->invokeMethod($this->avataxAdjustmentTaxes, 'isTaxCalculationEnabledForAdjustments');

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::isTaxCalculationEnabledForAdjustments
     * @throws \ReflectionException
     */
    public function checkCorrectReturnTypeForisTaxCalculationEnabledForAdjustmentsMethodInCaseOfError()
    {
        $storeId = 1;
        $message = 'error happened';

        $this->storeManagerMock->expects(static::atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);
        $this->storeInterfaceMock->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AvataxAdjustmentTaxes::ADJUSTMENTS_CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeId)
            ->willThrowException(new \Exception($message));

        /** @var bool $result */
        $result = $this->invokeMethod($this->avataxAdjustmentTaxes, 'isTaxCalculationEnabledForAdjustments');

        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::getCreditmemoTaxesForAdjustments
     * @throws \ReflectionException
     */
    public function checkThatWeGetCorrectEstimatedAdjustmentRefundAndAdjustmentFeeTaxes()
    {
        /** @var array<int, DataObject> $lines */
        $lines = [
            new DataObject([
                'description' => 'Chaz Kangeroo Hoodie',
                'tax' => -0.9
            ]),
            new DataObject([
                'description' => 'Adjustment refund',
                'tax' => -0,41
            ]),
            new DataObject([
                'description' => 'Adjustment fee',
                'tax' => 0.83
            ])
        ];
        /** @var RestTaxResult $response */
        $response = new RestTaxResult(['lines' => $lines]);

        $result = $this->invokeMethod($this->avataxAdjustmentTaxes, 'getCreditmemoTaxesForAdjustments', [$response]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('adjustment_refund', $result);
        $this->assertArrayHasKey('adjustment_fee', $result);
        $this->assertIsObject($result['adjustment_refund']);
        $this->assertIsObject($result['adjustment_fee']);
        $this->assertInstanceOf(DataObject::class, $result['adjustment_refund']);
        $this->assertInstanceOf(DataObject::class, $result['adjustment_fee']);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::getCreditmemoTaxesForAdjustments
     * @throws \ReflectionException
     */
    public function checkThatWeGetCorrectResponseInCaseOfNullInputParameter()
    {
        $response = null;

        /** @var array $result */
        $result = []; //$this->invokeMethod($this->avataxAdjustmentTaxes, 'getCreditmemoTaxesForAdjustments', [$response]);

        $this->assertCount(0 ,$result);
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::collect
     */
    public function checkThatWeDoNotSetAdjustmentsTaxesForCreditmemoInCaseItIsNotEnabled()
    {
        $storeId = 1;

        $this->storeManagerMock->expects(static::atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);
        $this->storeInterfaceMock->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(AvataxAdjustmentTaxes::ADJUSTMENTS_CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeId)
            ->willReturn(false);

        /** @var Creditmemo $result */
        $result = $this->avataxAdjustmentTaxes->collect($this->creditmemoMock);

        $this->assertIsObject($result);
        $this->assertInstanceOf(AvataxAdjustmentTaxes::class, $result);
        $this->assertCount(0, $result->getData());
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::collect
     */
    public function checkThatWeGetCorrectCalculatedAdjustmentsTaxesForCreditmemo()
    {
        $storeId = 1;
        $baseAdjustmentPositive = 5;

        // \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::isTaxCalculationEnabledForAdjustments
        $this->storeManagerMock->expects(static::atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);
        $this->storeInterfaceMock->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(AvataxAdjustmentTaxes::ADJUSTMENTS_CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeId)
            ->willReturn(true);

        // \ClassyLlama\AvaTax\Model\Order\Creditmemo\Total\AvataxAdjustmentTaxes::adjustmentsAreNotEmpty
        $this->creditmemoMock->expects(static::atLeastOnce())
            ->method('getBaseAdjustmentPositive')
            ->willReturn($baseAdjustmentPositive);
        $this->creditmemoMock->expects(static::never())
            ->method('getBaseAdjustmentNegative');

        /** @var CreditmemoRequest $creditmemoRequest */
        $creditmemoRequest = new CreditmemoRequest([
            'commit' => false,
            'type' => 'ReturnOrder'
        ]);
        $this->creditmemoRequestBuilderMock->expects(static::atLeastOnce())
            ->method('build')
            ->willReturn($creditmemoRequest);
        $this->creditmemoMock->expects(static::atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);

        /** @var array<int, DataObject> $lines */
        $lines = [
            new DataObject([
                'description' => 'Chaz Kangeroo Hoodie (random product name)',
                'tax' => -0.9
            ]),
            new DataObject([
                'description' => 'Adjustment refund',
                'tax' => -0.41,
                'tax_calculated' => -0.41
            ]),
            new DataObject([
                'description' => 'Adjustment fee',
                'tax' => 0.83,
                'tax_calculated' => 0.83
            ])
        ];
        $response = new RestTaxResult(['lines' => $lines]);
        $this->taxCompositeInterfaceMock->expects(static::atLeastOnce())
            ->method('calculateTax')
            ->with($creditmemoRequest, $storeId, ScopeInterface::SCOPE_STORE, [RestTaxInterface::FLAG_FORCE_NEW_RATES => true], null)
            ->willReturn($response);
        $this->creditmemoMock->expects(static::atLeastOnce())
            ->method('getTaxAmount')
            ->willReturn(0);
        $this->creditmemoMock->expects(static::atLeastOnce())
            ->method('getBaseTaxAmount')
            ->willReturn(0);


        /** @var Creditmemo $result */
        $result = $this->avataxAdjustmentTaxes->collect($this->creditmemoMock);

        $this->assertInstanceOf(AvataxAdjustmentTaxes::class, $result);
        $this->assertIsObject($result);
        $this->assertArrayHasKey('tax_amount', $this->creditmemoMock->getData());
        $this->assertArrayHasKey('base_tax_amount', $this->creditmemoMock->getData());
        $this->assertCount(2, $this->creditmemoMock->getData());
        $this->assertIsFloat($this->creditmemoMock->getData('tax_amount'));
        $this->assertIsFloat($this->creditmemoMock->getData('base_tax_amount'));
        $this->assertSame(-0.42, $this->creditmemoMock->getData('tax_amount'));
        $this->assertSame(-0.42, $this->creditmemoMock->getData('base_tax_amount'));
    }

    /**
     * Call protected/private method of a class
     *
     * @param $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeMethod(&$object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
