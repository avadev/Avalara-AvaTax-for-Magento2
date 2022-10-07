<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Framework\Interaction\Request\AddressBuilder;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Rest\Config as AvaTaxHelperRestConfig;
use ClassyLlama\AvaTax\Framework\Interaction\Address as InteractionAddress;
use ClassyLlama\AvaTax\Framework\Interaction\Address as FrameworkInteractionAddress;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelperConfig;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddress;

/**
 * Class AddressBuilderTest
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request
 */
class AddressBuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AddressBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressBuilder;

    /**
     * @var AvaTaxLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avaTaxLoggerMock;

    /**
     * @var AvaTaxHelperRestConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avaTaxHelperRestConfigMock;

    /**
     * @var InteractionAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $interactionAddressMock;

    /**
     * @var FrameworkInteractionAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frameworkInteractionAddressMock;

    /**
     * @var AvaTaxHelperConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avataxHelperConfigMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var OrderAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderAddressMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->avaTaxLoggerMock = $this->createMock(AvaTaxLogger::class);
        $this->avaTaxHelperRestConfigMock = $this->createMock(AvaTaxHelperRestConfig::class);
        $this->interactionAddressMock = $this->createMock(InteractionAddress::class);
        $this->frameworkInteractionAddressMock = $this->createMock(FrameworkInteractionAddress::class);
        $this->avataxHelperConfigMock = $this->createMock(AvaTaxHelperConfig::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderAddressMock = $this->getMockForAbstractClass(OrderAddress::class);
        $this->addressBuilder = $this->objectManager->getObject(AddressBuilder::class, [
            'avaTaxLogger' => $this->avaTaxLoggerMock,
            'avaTaxHelperRestConfig' => $this->avaTaxHelperRestConfigMock,
            'interactionAddress' => $this->interactionAddressMock,
            'frameworkInteractionAddress' => $this->frameworkInteractionAddressMock,
            'avataxHelperConfig' => $this->avataxHelperConfigMock
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\AddressBuilder::build
     */
    public function checkThatWeGetTheCorrectResponseType()
    {
        $storeId = 1;
        $mockData = $this->getMockData();
        $originMockAddress = array_shift($mockData);
        $addressTypeTo = array_pop($mockData);

        $this->orderMock->expects(static::atLeastOnce())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->orderMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->orderAddressMock);
        $this->interactionAddressMock->expects(static::once())
            ->method('getAddress')
            ->with($this->orderAddressMock)
            ->willReturn(new \Magento\Framework\DataObject($addressTypeTo));
        $this->avataxHelperConfigMock->expects(static::atLeastOnce())
            ->method('getOriginAddress')
            ->with($storeId)
            ->willReturn($originMockAddress);
        $this->frameworkInteractionAddressMock->expects(static::atLeastOnce())
            ->method('getAddress')
            ->with($originMockAddress)
            ->willReturn(new \Magento\Framework\DataObject($originMockAddress));
        $this->avaTaxHelperRestConfigMock->expects(static::once())
            ->method('getAddrTypeTo')
            ->willReturn(\Avalara\TransactionAddressType::C_SHIPTO);
        $this->avaTaxHelperRestConfigMock->expects(static::once())
            ->method('getAddrTypeFrom')
            ->willReturn(\Avalara\TransactionAddressType::C_SHIPFROM);


        /** @var array $addresses */
        $addresses = $this->addressBuilder->build($this->orderMock, $storeId);

        $this->assertArrayHasKey(\Avalara\TransactionAddressType::C_SHIPTO, $addresses);
        $this->assertArrayHasKey(\Avalara\TransactionAddressType::C_SHIPFROM, $addresses);
        $this->assertCount(2, $addresses);
        $this->assertIsArray($addresses);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $addresses[\Avalara\TransactionAddressType::C_SHIPTO]);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $addresses[\Avalara\TransactionAddressType::C_SHIPFROM]);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\AddressBuilder::build
     */
    public function checkThatWeGetAnEmptyArrayEvenIfAnExceptionWillBeThrown()
    {
        $storeId = 1;
        $message = 'something went wrong';
        $exception = new \Exception($message);

        $this->orderMock->expects(static::once())
            ->method('getIsVirtual')
            ->willThrowException($exception);

        /** @var array $addresses */
        $addresses = $this->addressBuilder->build($this->orderMock, $storeId);

        $this->assertCount(0, $addresses);
        $this->assertIsArray($addresses);
    }

    /**
     * Get mock data
     *
     * @return array
     */
    private function getMockData(): array
    {
        return [
            [
                'line_1' => null,
                'line_2' => null,
                'city' => null,
                'region_id' => '12',
                'postal_code' => '90034',
                'country' => 'US'
            ],
            [
                'line_1' => '900 Winslow Way E',
                'line_2' => '',
                'line_3' => '',
                'city' => 'Bainbridge Island',
                'region' => 'WA',
                'postal_code' => '98110-2450',
                'country' => 'US'
            ]
        ];
    }
}
