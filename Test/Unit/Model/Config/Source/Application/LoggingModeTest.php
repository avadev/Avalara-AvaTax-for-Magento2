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

namespace ClassyLlama\AvaTax\Model\Config\Source\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit Tests to cover LoggingMode class
 */
class LoggingModeTest extends TestCase
{
    const LOGGING_MODE_DB = 1;
    const LOGGING_MODE_FILE = 2;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * Setup method for creating necessary objects
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->modeModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application\LoggingMode::class,
            []
        );

		parent::setUp();
    }

    /**
    * tests Retrieve option array
    * @test
    * @covers ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application\LoggingMode::toOptionArray
    */
    public function testToOptionArray()
    {
        $options = [
            ['value' => self::LOGGING_MODE_DB, 'label' => __('Database')],
            ['value' => self::LOGGING_MODE_FILE, 'label' => __('File')]
        ];

        $this->assertEquals($options, $this->modeModel->toOptionArray());
    }

    /**
    * tests Retrieve option array
    * @test
    * @covers ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application\LoggingMode::toArray
    */
    public function testToArray()
    {
        $options = [self::LOGGING_MODE_DB => __('Database'),self::LOGGING_MODE_FILE => __('File')];

        $this->assertEquals($options, $this->modeModel->toArray());
    }

}