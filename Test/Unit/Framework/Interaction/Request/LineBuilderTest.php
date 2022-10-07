<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\LineBuilder
 */
class LineBuilderTest extends TestCase
{
    /**
     * Mock interactionLine
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Line|PHPUnit\Framework\MockObject\MockObject
     */
    private $interactionLine;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Request\LineBuilder
     */
    private $testObject;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $item;

    /**
     * @var MockObject|CreditmemoItemInterface
     */
    private $creditMemoItemMock;

    /**
     * creditmemoInterface instance
     *
     * @var \Magento\Sales\Api\Data\CreditmemoInterface
     */
    private $creditmemoInterface;

    /**
     * dataObject
     *
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var array
     */
    private $lines = [];

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->interactionLine = $this->createMock(\ClassyLlama\AvaTax\Framework\Interaction\Line::class);
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->creditMemoMock = $this->getMockForAbstractClass(\Magento\Sales\Api\Data\CreditmemoInterface::class);
        $this->creditMemoItemMock = $this->getMockBuilder(\Magento\Sales\Api\Data\CreditmemoItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->item = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item::class)
            ->addMethods(
                [
                    'isDeleted',
                    'getQtyToInvoice',
                    'getParentItemId',
                    'getQuoteItemId',
                    'getLockedDoInvoice',
                    'getProductId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Request\LineBuilder::class,
            [
                'interactionLine' => $this->interactionLine,
            ]
        );
    }

    /**
     * build method test
     */
    public function testBuild()
    {
        $orderItems = [$this->item];
        $this->creditMemoMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->creditMemoItemMock]);

        $this->interactionLine->expects($this->once())
            ->method('getLine')
            ->willReturn($this->dataObject);

        $linarray = $this->testObject->build($this->creditMemoMock,$orderItems);

        $this->assertIsArray($linarray);
    }
}
