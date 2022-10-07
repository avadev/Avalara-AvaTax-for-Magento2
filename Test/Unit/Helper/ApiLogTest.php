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
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\App\Helper\Context;
use ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger;

/**
 * Class ApiLogTest
 * @covers \ClassyLlama\AvaTax\Helper\ApiLog
 * @package ClassyLlama\AvaTax\Test\Unit\Helper
 */
class ApiLogTest extends TestCase
{
    /**
     * Core store config
     *
     * @var Config
     */
    protected $config;

    /**
     * Core store config
     *
     * @var Context
     */
    protected $context;

    /**
     * Core store config
     *
     * @var GenericLogger
     */
    protected $genericLogger;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Helper\ApiLog
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->config = $this->getMockBuilder(Config::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->config->expects($this->any())
                                ->method('isProductionMode')
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('getAccountNumber')
                                ->willReturn("1234567890");
        $this->config->expects($this->any())
                                ->method('getLicenseKey')
                                ->willReturn("secretdontreveal");
        
        $this->context = $this->getMockBuilder(Context::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->genericLogger = $this->getMockBuilder(GenericLogger::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\ApiLog::class,
            [
                'config' => $this->config,
                'context' => $this->context,
                'genericLogger' => $this->genericLogger
            ]
        );
    }

    /**
     * tests testConnectionLog
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::testConnectionLog
     */
    public function testTestConnectionLog()
    {
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willReturn(true);
        $message = "Test Connection Message";
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->testConnectionLog($message, $scope, $scopeType);
    }

    /**
     * tests testConnectionLog Exception
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::testConnectionLog
     */
    public function testTestConnectionLogException()
    {
        $e = $this->objectManager->getObject(\Exception::class);
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willThrowException($e);
        $message = "Test Connection Message";
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->testConnectionLog($message, $scope, $scopeType);
    }

    /**
     * tests configSaveLog
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::configSaveLog
     */
    public function testConfigSaveLog()
    {
        $this->config->expects($this->any())
                                ->method('getConfigData')
                                ->willReturn([
                                    "production_license_key" => "1234567890",
                                    "development_license_key" => "1234567890",
                                    "test_configuration" => ["html_data" => "<p>test message</p>"]
                                ]);
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willReturn(true);
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->configSaveLog($scope, $scopeType);
    }

    /**
     * tests configSaveLog Exception
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::configSaveLog
     */
    public function testConfigSaveLogException()
    {
        $this->config->expects($this->any())
                                ->method('getConfigData')
                                ->willReturn([]);
        $e = $this->objectManager->getObject(\Exception::class);
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willThrowException($e);
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->configSaveLog($scope, $scopeType);
    }

    /**
     * tests makeTransactionRequestLog
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::makeTransactionRequestLog
     */
    public function testMakeTransactionRequestLog()
    {
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willReturn(true);
        $logContext = [
            "extra" => [
                "ConnectorTime" => ["start" => "1", "end" => "2"],
                "ConnectorLatency" => ["start" => "3", "end" => "1"]
            ] 
        ];
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->makeTransactionRequestLog($logContext, $scope, $scopeType);
    }

    /**
     * tests makeTransactionRequestLog Exception
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::makeTransactionRequestLog
     */
    public function testMakeTransactionRequestLogException()
    {
        $e = $this->objectManager->getObject(\Exception::class);
        $this->genericLogger->expects($this->any())
                                ->method('apiLog')
                                ->willThrowException($e);
        $logContext = [
            "extra" => [
                "ConnectorTime" => ["start" => "1"],
                "ConnectorLatency" => ["start" => "1", "end" => "0"]
            ] 
        ];
        $scope = NULL;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->testObject->makeTransactionRequestLog($logContext, $scope, $scopeType);
    }

    /**
     * tests getLatencyTimeAndConnectorTime
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ApiLog::getLatencyTimeAndConnectorTime
     */
    public function testGetLatencyTimeAndConnectorTime()
    {
        $logContext = [];
        $this->testObject->getLatencyTimeAndConnectorTime($logContext);
    }
}
