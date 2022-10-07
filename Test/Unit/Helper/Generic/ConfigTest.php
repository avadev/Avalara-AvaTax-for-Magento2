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

namespace ClassyLlama\AvaTax\Test\Unit\Helper\Generic;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use TestDeferred\TestClass;

/**
 * Class ConfigTest
 * @covers ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Helper\Generic
 */
class ConfigTest extends TestCase
{
    const API_LOG_TYPE_PERFORMANCE = 'performance';
    const API_LOG_TYPE_DEBUG = 'debug';
    const API_LOG_TYPE_CONFIG = 'config';
    
    const API_LOG_LEVEL_ERROR = 'error';
    const API_LOG_LEVEL_EXCEPTION = 'exception';
    const API_LOG_LEVEL_INFO = 'info';

    /**
     * Api Log Types
     */
    const API_LOG_TYPE = [
        self::API_LOG_TYPE_PERFORMANCE => 'Performance', 
        self::API_LOG_TYPE_DEBUG => 'Debug', 
        self::API_LOG_TYPE_CONFIG => 'ConfigAudit'
    ];

    /**
     * Api Log Levels
     */
    const API_LOG_LEVEL = [
        self::API_LOG_LEVEL_ERROR => 'Error', 
        self::API_LOG_LEVEL_EXCEPTION => 'Exception', 
        self::API_LOG_LEVEL_INFO => 'Informational'
    ];

    /**
     * Sandbox API URL for LOGGER
     */
    const ENV_LOGGER_SANDBOX_BASE_URL = 'https://ceplogger.sbx.avalara.com';

    /**
     * Production API URL for LOGGER
     */
    const ENV_LOGGER_PRODUCTION_BASE_URL = 'https://ceplogger.avalara.com';

    /**
     * Endpoint for logger
     */
    const API_LOGGER_ENDPOINT = '/api/logger/';

    /**
     * String value of API mode
     */
    const API_MODE_PRODUCTION = 'production';

    /**
     * String value of API mode
     */
    const API_MODE_SANDBOX = 'sandbox';

    /**
     * ERP details of application
     */
    const ERP_DETAILS = "MAGENTO";

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->productMetadataMock = $this->getMockBuilder(\Magento\Framework\App\ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendUrlMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->timezoneConverterMock = $this->getMockBuilder(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
            )->setMethods(['date'])->getMockForAbstractClass();

        $this->configHelper = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::class,
            [
                'context' => $this->context,
                'mageMetadata' => $this->productMetadataMock,
                'backendUrl' => $this->backendUrlMock,
                'encryptor' => $this->encryptorMock,
                'timeZone' => $this->timezoneConverterMock
            ]
        );
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        parent::setUp();
    }

    /**
     * tests get getTimeZoneObject
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::getTimeZoneObject
     */
    public function testGetTimeZoneObject()
    {
        $this->assertEquals($this->timezoneConverterMock, $this->configHelper->getTimeZoneObject());
    }

    /**
     * tests Application Name
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::getApplicationName
     */
    public function testGetApplicationName()
    {   
        $this->assertIsString($this->configHelper->getApplicationName());
    }

    /**
     * tests get getMachineName
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::getMachineName
     */
    public function testGetMachineName()
    {        
        $this->assertIsString($this->configHelper->getMachineName());
    }

    /**
     * tests Basic Auth Token prepareBasicAuthToken
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::prepareBasicAuthToken
     */
    public function testPrepareBasicAuthToken()
    {
        $accountNumber = "";
        $accountSecret = "";
        $authToken = base64_encode($accountNumber . ":" . $accountSecret);
        $this->assertEquals($authToken, $this->configHelper->prepareBasicAuthToken($accountSecret, $accountSecret));
    }


}