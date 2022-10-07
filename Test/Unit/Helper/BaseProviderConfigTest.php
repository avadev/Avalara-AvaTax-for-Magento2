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
 * Class ConfigTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Config
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Helper
 */
class BaseProviderConfigTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    
    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Config::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Helper\Config::class,
            []
        );
        parent::setUp();
    }

    /**
     * tests get getBatchSize
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Config::getBatchSize
     */
    public function testGetBatchSize()
    {
        $storecode = self::STORE_CODE;
        $this->assertIsInt($this->configHelper->getBatchSize($storecode, self::SCOPE_STORE));
    }
    
    /**
     * tests get getQueueLimit
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Helper\Config::getQueueLimit
     */
    public function testGetQueueLimit()
    {
        $storecode = self::STORE_CODE;
        $this->assertIsInt($this->configHelper->getQueueLimit($storecode, self::SCOPE_STORE));
    }
}
