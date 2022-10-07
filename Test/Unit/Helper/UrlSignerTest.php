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

/**
 * Class UrlSignerTest
 * @covers \ClassyLlama\AvaTax\Helper\UrlSigner
 * @package ClassyLlama\AvaTax\Test\Unit\Helper
 */
class UrlSignerTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    
    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Helper\UrlSigner::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->configMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlSignerHelper = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\UrlSigner::class,
            [
                'config' => $this->configMock,
                'context' => $this->context
            ]
        );
        parent::setUp();
    }

    /**
     * tests signParameters
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\UrlSigner::signParameters
     */
    public function testSignParameters()
    {
        $storecode = self::STORE_CODE;
        $parameters = [];
        $parameters = [
            'certificate_id' => 123,
            'customer_id' => 1,
            'expires' => time()
        ];
        ksort($parameters);
        $this->assertEquals(hash_hmac('sha256', http_build_query($parameters), $this->configMock->getLicenseKey(self::SCOPE_STORE, $storecode)),$this->urlSignerHelper->signParameters($parameters));
    }
}
