<?php

/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Tests\Integration\Helper;

use ClassyLlama\AvaTax\Helper\CustomsConfig as CustomsConfigHelper;

/**
 * AvaTax Config model
 */
class CustomsConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CustomsConfigHelper
     */
    protected $customsHelper;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customsHelper = $this->objectManager->get(CustomsConfigHelper::class);
    }

    /**
     * @magentoConfigFixture current_store tax/avatax_customs/enabled 1
     * @magentoConfigFixture current_store tax/avatax/enabled 1
     */
    public function testEnabled_enabledReturnsTrue()
    {
        $result = $this->customsHelper->enabled();
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture current_store tax/avatax_customs/enabled 0
     * @magentoConfigFixture current_store tax/avatax/enabled 1
     */
    public function testEnabled_disabledReturnsFalse()
    {
        $result = $this->customsHelper->enabled();
        $this->assertFalse($result);
    }

    /**
     * @magentoConfigFixture current_store tax/avatax_customs/enabled 1
     * @magentoConfigFixture current_store tax/avatax/enabled 0
     */
    public function testEnabled_avaTaxDisabledReturnsFalse()
    {
        $result = $this->customsHelper->enabled();
        $this->assertFalse($result);
    }

    /**
     * @magentoConfigFixture default_store tax/avatax_customs/enabled 1
     * @magentoConfigFixture current_store tax/avatax/enabled 1
     * @magentoConfigFixture current_store tax/avatax_customs/enabled 0
     */
    public function testEnabled_storeOverridesDefault()
    {
        $result = $this->customsHelper->enabled();
        $this->assertFalse($result);
    }
}
