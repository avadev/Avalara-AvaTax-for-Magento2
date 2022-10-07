<?php
namespace ClassyLlama\AvaTax\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
//use Magento\Framework\DataObjectFactory;

/**
 * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver
 */
class ConfigSaveObserverTest extends TestCase
{
    /**
     * Mock messageManager
     *
     * @var \Magento\Framework\Message\ManagerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Mock customsConfig
     *
     * @var \ClassyLlama\AvaTax\Helper\CustomsConfig|PHPUnit\Framework\MockObject\MockObject
     */
    private $customsConfig;

    /**
     * Mock moduleChecks
     *
     * @var \ClassyLlama\AvaTax\Helper\ModuleChecks|PHPUnit\Framework\MockObject\MockObject
     */
    private $moduleChecks;

    /**
     * Mock interactionRest
     *
     * @var \ClassyLlama\AvaTax\Api\RestInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $interactionRest;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Observer\ConfigSaveObserver
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::__construct
     * {@inheritDoc}
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->config = $this->createMock(\ClassyLlama\AvaTax\Helper\Config::class);
        $this->customsConfig = $this->createMock(\ClassyLlama\AvaTax\Helper\CustomsConfig::class);
        $this->moduleChecks = $this->createMock(\ClassyLlama\AvaTax\Helper\ModuleChecks::class);
        $this->interactionRest = $this->createMock(\ClassyLlama\AvaTax\Api\RestInterface::class);
        $this->observerObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::class,
            [
                'messageManager' => $this->messageManager,
                'config' => $this->config,
                'customsConfig' => $this->customsConfig,
                'moduleChecks' => $this->moduleChecks,
                'interactionRest' => $this->interactionRest,
            ]
        );
    }

   

    /**
     * tests execute
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testExecute()
    {

        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig->expects($this->any())
                            ->method('getGroundShippingMethods')                            
                            ->willReturn(['test']);

        $this->customsConfig->expects($this->any())
                            ->method('getOceanShippingMethods')                            
                            ->willReturn(['test']);

        $this->customsConfig->expects($this->any())
                            ->method('getAirShippingMethods')                            
                            ->willReturn(['test']);
        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn(null);

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn("default");

         $this->interactionRest
            ->expects($this->any())
            ->method('ping')
            ->willReturn(true);

        $this->observerObject->execute($observerStore);
    }

    /**
     * tests execute for if else part
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testIfElseExecute()
    {

        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig->expects($this->any())
                            ->method('getGroundShippingMethods')                            
                            ->willReturn(['test']);

        $this->customsConfig->expects($this->any())
                            ->method('getOceanShippingMethods')                            
                            ->willReturn(['test']);

        $this->customsConfig->expects($this->any())
                            ->method('getAirShippingMethods')                            
                            ->willReturn(['test']);

        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn(null);

        $this->observerObject->execute($observerStore);
    }

    /**
     * tests execute for else part
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testElseExecute()
    {
        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig
            ->expects($this->any())
            ->method('getGroundShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getOceanShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getAirShippingMethods')                            
            ->willReturn(['test']);

        $this->config
            ->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);

        $this->config
            ->expects($this->any())
            ->method('isProductionMode')
            ->willReturn(0);

        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn(null);

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn(null);

        $this->observerObject->execute($observerStore);
    }

    /**
     * tests execute for sendping if result true
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testElseExecuteWithPing()
    {
        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig
            ->expects($this->any())
            ->method('getGroundShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getOceanShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getAirShippingMethods')                            
            ->willReturn(['test']);

        $this->config
            ->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);

        $this->config
            ->expects($this->any())
            ->method('isProductionMode')
            ->willReturn(0);

        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn(1);

        $this->interactionRest->expects($this->any())
            ->method('ping')
            ->willReturn(true);

        $this->observerObject->execute($observerStore);
    }

    /**
     * tests execute for checkCredentialsForMode if result is false
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testFalseCheckCredentialsForMode()
    {
        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig
            ->expects($this->any())
            ->method('getGroundShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getOceanShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getAirShippingMethods')                            
            ->willReturn(['test']);

        $this->config
            ->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);

        $this->config
            ->expects($this->any())
            ->method('isProductionMode')
            ->willReturn(0);

        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn(1);

        $this->interactionRest->expects($this->any())
            ->method('ping')
            ->willReturn(true);

        $this->config
            ->expects($this->any())
            ->method('getAccountNumber')
            ->willReturn('');

        $this->observerObject->execute($observerStore);
    }

    /**
     * tests execute for checkCredentialsForMode if result is false
     * @test
     * @covers \ClassyLlama\AvaTax\Observer\ConfigSaveObserver::execute
     */
    public function testPingException()
    {
        $dataObj = new \Magento\Framework\DataObject();

        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkNativeTaxRules'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $this->moduleChecks->expects($this->any())
            ->method('checkNativeTaxRules')
            ->willReturn(
                ["store"]
        );

        $className = get_class($this->observerObject);
        $reflection = new \ReflectionClass($className);
        $noticesList = $reflection->getMethod('getNotices');
        $noticesList->setAccessible(true);

        $noticesList->invoke($this->observerObject);

        $this->customsConfig
            ->expects($this->any())
            ->method('getGroundShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getOceanShippingMethods')                            
            ->willReturn(['test']);

        $this->customsConfig
            ->expects($this->any())
            ->method('getAirShippingMethods')                            
            ->willReturn(['test']);

        $this->config
            ->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);

        $this->config
            ->expects($this->any())
            ->method('isProductionMode')
            ->willReturn(0);

        $observerStore = $this->getMockBuilder(Observer::class)
            ->addMethods(['getStore','checkConflictingShippingMethods','getWebsite'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerStore->expects($this->any())
            ->method('getStore')
            ->willReturn("default");

        $observerStore->expects($this->any())
            ->method('getWebsite')
            ->willReturn(1);

        $e = $this->objectManager->getObject(\Magento\Framework\Exception\LocalizedException::class);
        
        $this->interactionRest->expects($this->any())
            ->method('ping')
            ->willThrowException($e);

        $this->observerObject->execute($observerStore);
    }
   
}
