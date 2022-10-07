<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Framework\Interaction\Request\TaxComposite;
use ClassyLlama\AvaTax\Framework\Interaction\Storage\ResultStorage;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Model\Factory\TransactionBuilderFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory as TaxResultFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as RestTaxResult;

/**
 * Class TaxCompositeTest
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request
 */
class TaxCompositeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TaxComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxComposite;

    /**
     * @var ResultStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultStorageMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * @var ClientPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientPoolMock;

    /**
     * @var TransactionBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionBuilderFactoryMock;

    /**
     * @var TaxResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxResultFactoryMock;

    /**
     * @var RestConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restConfigMock;

    /**
     * @var CustomsConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customsConfigMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestInterfaceMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resultStorageMock = $this->createMock(ResultStorage::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->dataObjectFactoryMock = $this->getMockBuilder(DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientPoolMock = $this->createMock(ClientPool::class);
        $this->transactionBuilderFactoryMock = $this->getMockBuilder(TransactionBuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxResultFactoryMock = $this->getMockBuilder(TaxResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restConfigMock = $this->createMock(RestConfig::class);
        $this->customsConfigMock = $this->createMock(CustomsConfig::class);
        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'unsetData'])
            ->getMock();
        $this->taxComposite = $this->objectManager->getObject(TaxComposite::class, [
            'resultStorage' => $this->resultStorageMock,
            'logger' => $this->loggerMock,
            'dataObjectFactory' => $this->dataObjectFactoryMock,
            'clientPool' => $this->clientPoolMock,
            'transactionBuilderFactory' => $this->transactionBuilderFactoryMock,
            'taxResultFactory' => $this->taxResultFactoryMock,
            'restConfig' => $this->restConfigMock,
            'customsConfigHelper' => $this->customsConfigMock
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\TaxComposite::calculateTax
     */
    public function checkThatWeGetCorrectResponseWithTaxesFromTheCache()
    {
        $storeId = 1;

        /** @var \Magento\Framework\DataObject $taxes */
        $taxes = new RestTaxResult($this->getMockData());

        $this->requestInterfaceMock->expects(static::atLeastOnce())
            ->method('hasData')
            ->with('code')
            ->willReturn(true);
        $this->requestInterfaceMock->expects(static::atLeastOnce())
            ->method('unsetData')
            ->with('code')
            ->willReturnSelf();
        $this->resultStorageMock->expects(static::atLeastOnce())
            ->method('find')
            ->willReturn($taxes);

        /** @var RestTaxResult $collectedTaxes */
        $collectedTaxes = $this->taxComposite->calculateTax(
            $this->requestInterfaceMock,
            $storeId,
            ScopeInterface::SCOPE_STORE,
            [RestTaxInterface::FLAG_FORCE_NEW_RATES => true],
            null
        );

        $this->assertInstanceOf(RestTaxResult::class, $collectedTaxes);
        $this->assertIsObject($collectedTaxes);
        $this->assertArrayHasKey('cache', $collectedTaxes->getData());
        $this->assertArrayHasKey('total_tax', $collectedTaxes->getData());
        $this->assertArrayHasKey('total_tax_calculated', $collectedTaxes->getData());
    }

    /**
     * Get mock data
     *
     * @return array
     */
    private function getMockData(): array
    {
        return [
            'total_amount' => -158.0,
            'total_tax' => -14.24,
            'total_taxable' => -158.0,
            'total_tax_calculated' => -14.24,
            'creation_timestamp' => 1568901642,
            'cache' => true
        ];
    }
}
