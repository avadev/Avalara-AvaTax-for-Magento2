<?php
/*
 *
 * Avalara_BaseProvider
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

namespace ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Queue;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ViewTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\View
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block
 */

class ViewTest extends TestCase
{
    const STATUS = 'Test';

    /**
     * Setup
     * @covers ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Queue\ViewTest
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Widget\Context::class)
            ->setMethods(['getUrlBuilder','getButtonList'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->getMock();

        $this->queueStatusMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status::class)
            ->getMock();
            
        $this->buttonListMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Button\ButtonList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMock();
    
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->context->expects($this->once())
            ->method('getButtonList')
            ->willReturn($this->buttonListMock);

        $this->blockMock = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\View::class,
            [
                "context" => $this->context,
                "coreRegistry" => $this->registryMock,
                "queueStatus" => $this->queueStatusMock,
                'data' => []
            ]
        );

        parent::setUp();
    }

    /**
     * tests getQueue
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\View::getQueue
     */
    public function testGetQueue()
    {
        $returnQueueMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\Queue::class)
        ->disableOriginalConstructor()
        ->getMock();

        $url = "https://localhost/BaseProvider/queue/index";

        $this->urlBuilderMock->expects($this->any())
        ->method('getUrl')
        ->willReturn($url);

        $this->registryMock->expects($this->once())
        ->method('registry')
        ->with('current_queue')
        ->willReturn($returnQueueMock);

        $this->assertEquals($returnQueueMock, $this->blockMock->getQueue());
    }

    /**
     * tests getStatusLabel
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Queue\View::getStatusLabel
     */
    public function testGetStatusLabel()
    {
        $status = self::STATUS;
        $this->assertIsString($this->blockMock->getStatusLabel($status));
    }
}