<?php
/*
 *
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class SalesModelServiceQuoteSubmitBeforeTest
 * @covers \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore
 * @package ClassyLlama\AvaTax\Observer
 */
class SalesModelServiceQuoteSubmitBeforeTest extends TestCase
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger
     */
    protected $extensionAttributeMerger;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->extensionAttributeMerger = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class)->disableOriginalConstructor()
                                ->getMock();
        $this->extensionAttributeMerger
            ->expects($this->any())
            ->method('copyAttributes')
            ->willReturn([]);
        
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore::class,
                [
                    'extensionAttributeMerger' => $this->extensionAttributeMerger
                ]
            );
    }

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore::execute
     */
    public function testExecuteNoArray()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $order
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([]);
        $observer->setOrder($order);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->getMock();
        $quote
            ->expects($this->any())
            ->method('getItems')
            ->willReturn(false);
        $observer->setQuote($quote);

        $args = array("order", "quote");
        $returnValues = array($order,$quote);
        foreach ($args as $key=>$arg) {
            $observer->expects($this->at($key))
                 ->method('getData')
                 ->with($arg)
                 ->willReturn($returnValues[$key]);
        }

        $this->testObject->execute($observer);
    }

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\SalesModelServiceQuoteSubmitBefore::execute
     */
    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->disableOriginalConstructor()
            ->getMock();

        $orderItem = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)->disableOriginalConstructor()
            ->getMock();
        $orderItem
            ->expects($this->any())
            ->method('getQuoteItemId')
            ->willReturn(1);
        $orderItem2 = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)->disableOriginalConstructor()
            ->getMock();
        $orderItem2
            ->expects($this->any())
            ->method('getQuoteItemId')
            ->willReturn(2);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $order
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$orderItem, $orderItem2]);
        $observer->setOrder($order);

        $cartItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)->disableOriginalConstructor()
            ->getMock();
        $cartItem
            ->expects($this->any())
            ->method('getItemId')
            ->willReturn(1);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->getMock();
        $quote
            ->expects($this->any())
            ->method('getItems')
            ->willReturn([$cartItem]);
        $observer->setQuote($quote);

        $args = array("order", "quote");
        $returnValues = array($order,$quote);
        foreach ($args as $key=>$arg) {
            $observer->expects($this->at($key))
                 ->method('getData')
                 ->with($arg)
                 ->willReturn($returnValues[$key]);
        }

        $this->testObject->execute($observer);
    }
}
