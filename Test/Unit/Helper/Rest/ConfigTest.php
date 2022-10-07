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
namespace ClassyLlama\AvaTax\Test\Unit\Helper\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ConfigTest
 * @covers \ClassyLlama\AvaTax\Helper\Rest\Config
 * @package ClassyLlama\AvaTax\Test\Unit\Helper\Rest
 */
class ConfigTest extends TestCase
{
    /**
     * Mock context
     *
     * @var \Magento\Framework\App\Helper\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Mock restInteraction
     *
     * @var \ClassyLlama\AvaTax\Api\RestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $restInteraction;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Helper\Rest\Config
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->restInteraction = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Helper\Rest\Config::class,
            [
                'context' => $this->context,
                'restInteraction' => $this->restInteraction,
            ]
        );
    }

    /**
     * tests getDocTypeQuote
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getDocTypeQuote
     */
    public function testGetDocTypeQuote()
    {
        $this->assertEquals(0, $this->testObject->getDocTypeQuote());
    }

    /**
     * tests getDocTypeInvoice
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getDocTypeInvoice
     */
    public function testGetDocTypeInvoice()
    {
        $this->assertEquals(1, $this->testObject->getDocTypeInvoice());
    }

    /**
     * tests getDocTypeCreditmemo
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getDocTypeCreditmemo
     */
    public function testGetDocTypeCreditmemo()
    {
        $this->assertEquals(5, $this->testObject->getDocTypeCreditmemo());
    }

    /**
     * tests getDocStatusCommitted
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getDocStatusCommitted
     */
    public function testGetDocStatusCommitted()
    {
        $this->assertEquals(3, $this->testObject->getDocStatusCommitted());
    }

    /**
     * tests getAddrTypeFrom
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getAddrTypeFrom
     */
    public function testGetAddrTypeFrom()
    {
        $this->assertIsString($this->testObject->getAddrTypeFrom());
    }

    /**
     * tests getAddrTypeTo
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getAddrTypeTo
     */
    public function testGetAddrTypeTo()
    {
        $this->assertIsString($this->testObject->getAddrTypeTo());
    }
    /**
     * tests getAddrTypeBillTo
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getAddrTypeBillTo
     */
    public function testGetAddrTypeBillTo()
    {
        $this->assertIsString($this->testObject->getAddrTypeBillTo());
    }

    /**
     * tests getOverrideTypeDate
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getOverrideTypeDate
     */
    public function testGetOverrideTypeDate()
    {
        $this->assertIsInt($this->testObject->getOverrideTypeDate());
    }

    /**
     * tests getTextCaseMixed
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getTextCaseMixed
     */
    public function testGetTextCaseMixed()
    {
        $this->assertIsInt($this->testObject->getTextCaseMixed());
    }

    /**
     * tests getErrorSeverityLevels
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getErrorSeverityLevels
     */
    public function testGetErrorSeverityLevels()
    {
        $this->assertIsArray($this->testObject->getErrorSeverityLevels());
    }

    /**
     * tests getWarningSeverityLevels
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\Rest\Config::getWarningSeverityLevels
     */
    public function testGetWarningSeverityLevels()
    {
        $this->assertIsArray($this->testObject->getWarningSeverityLevels());
    }
}
