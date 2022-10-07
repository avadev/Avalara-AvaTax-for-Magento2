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
namespace ClassyLlama\AvaTax\Test\Unit\Observer\Model\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AfterSaveObserverExceptionTest
 * @covers \ClassyLlama\AvaTax\Observer\Model\Customer\AfterSaveObserver
 * @package ClassyLlama\AvaTax\Observer\Model\Customer
 */
class AfterSaveObserverExceptionTest extends TestCase
{
    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $restCustomerInterface;

    /**
     * @var \ClassyLlama\AvaTax\Helper\DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Customer
     */
    protected $customerHelper;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Observer\CalculateVirtualOrder::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->restCustomerInterface = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestCustomerInterface::class)->disableOriginalConstructor()
                    ->getMock(); 
        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)->disableOriginalConstructor()
                    ->getMock();
        $this->restCustomerInterface
            ->expects($this->any())
            ->method('updateCustomer')
            ->willReturn($dataObject);
        $this->documentManagementConfig = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\DocumentManagementConfig::class)->disableOriginalConstructor()
                    ->getMock();
        $this->documentManagementConfig
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)->disableOriginalConstructor()
                    ->getMock();
        $this->appState
            ->expects($this->any())
            ->method('getAreaCode')
            ->willReturn('adminhtml');
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->disableOriginalConstructor()
                    ->getMock();
        $errorMessage = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->disableOriginalConstructor()
                    ->getMock();
        $this->messageManager
            ->expects($this->any())
            ->method('addErrorMessage')
            ->willReturn($errorMessage);
        $this->avaTaxLogger = $this->getMockBuilder(\ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger::class)->disableOriginalConstructor()
                    ->getMock();
        $this->cache = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)->disableOriginalConstructor()
                    ->getMock();
        $this->cache
            ->expects($this->any())
            ->method('clean')
            ->willReturn(true);
        $this->customerHelper = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Customer::class)->disableOriginalConstructor()
                    ->getMock();
        $e = $this->objectManager->getObject(\Exception::class);
        $this->customerHelper
            ->expects($this->any())
            ->method('getCustomerCode')
            ->willThrowException($e);

        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Observer\Model\Customer\AfterSaveObserver::class,
            [
                "restCustomerInterface" => $this->restCustomerInterface,
                "documentManagementConfig" => $this->documentManagementConfig,
                "appState" => $this->appState,
                "messageManager" => $this->messageManager,
                "avaTaxLogger" => $this->avaTaxLogger,
                "cache" => $this->cache,
                "customerHelper" => $this->customerHelper
            ]
        );
    }

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\Model\Customer\AfterSaveObserver::execute
     */
    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $customer
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $observer
            ->expects($this->any())
            ->method('getData')
            ->willReturn($customer);
        $this->testObject->execute($observer);
    }
}
