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

namespace ClassyLlama\AvaTax\Test\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class GenericLogger
 * @covers \ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Logger
 */
class GenericLoggerTest extends TestCase
{
    const API_LOG = 1;

	protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);

        $name = 'generic_logger';
        $processors = [];
        $handlers[1] = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Logger\Handler\Generic\ApiHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $processors[1] = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Logger\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->genericLogger = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger::class,
            [
                'name' => $name,
                'handlers' => $handlers,
                'processors' => $processors
            ]
        );
        
		parent::setUp();
    }

    /**
     * tests addApiLog
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger::addApiLog
     */
    public function testAddApiLog()
    {
        $message = "Test log text.";
        $this->assertIsBool($this->genericLogger->addApiLog($message));
    }

    /**
     * tests apiLog
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger::apiLog
     */
    public function testApiLog()
    {
        $message = "Test log text.";
        $this->assertIsBool($this->genericLogger->apiLog($message));
    }

}