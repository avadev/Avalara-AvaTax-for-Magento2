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

namespace ClassyLlama\AvaTax\Test\Unit\Helper\Application;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use TestDeferred\TestClass;

/**
 * Class ConfigTest
 * @covers ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Helper\Application
 */
class ConfigTest extends TestCase
{
    const XML_PATH_APPLICATION_LOG_ENABLED = 'tax/baseprovider_logger/logging_enabled';
    const XML_PATH_AVATAX_APPLICATION_LOG_MODE = 'tax/baseprovider_logger/logging_mode';
    const XML_PATH_AVATAX_APPLICATION_LOG_LIMIT = 'tax/baseprovider_logger/logging_limit';

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config
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
            \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::class,
            []
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
     * tests whether log is enabled 
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::getLogEnabled
     */
    public function testGetLogEnabled()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_APPLICATION_LOG_ENABLED,
            )
            ->willReturn(true);
        $this->assertIsInt($this->configHelper->getLogEnabled());
    }

    /**
     * tests whether log is disabled 
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::getLogEnabled
     */
    public function testGetLogEnabledFalse()
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                self::XML_PATH_APPLICATION_LOG_ENABLED,
            )
            ->willReturn(false);
        $this->assertIsInt($this->configHelper->getLogEnabled());
    }

    /**
     * tests if log Mode
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::getLogMode
     */
    public function testGetLogMode()
    {
        // 1 for "Database" and 2 for "File"
        $int = '1';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_APPLICATION_LOG_MODE)
            ->willReturn($int);
        $this->assertIsInt($this->configHelper->getLogMode());
    }

    /**
     * tests if log Mode is "File"
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::getLogMode
     */
    public function testGetLogModeFile()
    {
        // 1 for "Database" and 2 for "File"
        $int = '2';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_APPLICATION_LOG_MODE)
            ->willReturn($int);
        $this->assertIsInt($this->configHelper->getLogMode());
    }

    /**
     * tests log limit 
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::getLogLimit
     */
    public function testGetLogLimit()
    {
        $int = '10';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_APPLICATION_LOG_LIMIT)
            ->willReturn($int);
        $this->assertIsInt($this->configHelper->getLogLimit());
    }
}