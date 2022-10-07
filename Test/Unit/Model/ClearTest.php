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

namespace ClassyLlama\AvaTax\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ClearTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Log\Clear
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model
 */
class ClearTest extends TestCase
{
    const XML_PATH_AVATAX_APPLICATION_LOG_MODE = 'tax/baseprovider_logger/logging_limit';

	protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);
        
        $this->applicationLoggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->applicationLoggerConfigMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->applicationLoggerConfigMock->expects($this->once())
            ->method('getLogLimit')
            ->willReturn(1);
        
        $this->logCollMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\Collection::class)
            ->disableOriginalConstructor()
            ->getMock(); 
        $this->logCollMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturn([]);
        $this->logCollFactoryMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logCollFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->logCollMock);

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Model\Log\Clear::class,
            [
                'applicationLogger' => $this->applicationLoggerMock,
                'applicationLoggerConfig' => $this->applicationLoggerConfigMock,
                'logCollFactory' => $this->logCollFactoryMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
		parent::setUp();
    }

    /**
     * tests log clear process
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Log\Clear::process
     */
    public function testProcess()
    {
        $this->assertNull($this->logModel->process());
    }

    /**
     * tests log clear clearDbLogs
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Log\Clear::clearDbLogs
     */
    public function testClearDbLogs()
    {
        $this->assertIsInt($this->logModel->clearDbLogs());
    }
}