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
 * Class PreventOrderPlaceObserverTest
 * @covers \ClassyLlama\AvaTax\Observer\PreventOrderPlaceObserver
 * @package ClassyLlama\AvaTax\Observer
 */
class PreventOrderPlaceObserverTest extends TestCase
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Observer\PreventOrderPlaceObserver
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Observer\PreventOrderPlaceObserver::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)->disableOriginalConstructor()
                                ->getMock();
        $this->coreRegistry
            ->expects($this->any())
            ->method('registry')
            ->willReturn(false);
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)->disableOriginalConstructor()
                                ->getMock();
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Observer\PreventOrderPlaceObserver::class,
                [
                    'coreRegistry' => $this->coreRegistry,
                    'config' => $this->config
                ]
            );
    }

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\PreventOrderPlaceObserver::execute
     */
    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)->disableOriginalConstructor()
            ->getMock();
        $order
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $observer->setOrder($order);
        $this->testObject->execute($observer);
    }
}
