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

namespace ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ViewTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\View
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block
 */

class ViewTest extends TestCase
{
    /**
     * Setup
     * @covers ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log\ViewTest
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
            \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\View::class,
            [
                "context" => $this->context,
                "coreRegistry" => $this->registryMock,
                'data' => []
            ]
        );

        parent::setUp();
    }

    /**
     * tests getLog
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\View::getLog
     */
    public function testGetLog()
    {
        $returnLogMock = $this->getMockBuilder(\ClassyLlama\AvaTax\BaseProvider\Model\Log::class)
        ->disableOriginalConstructor()
        ->getMock();

        $url = "https://localhost/GenericLogger/log/index";

        $this->urlBuilderMock->expects($this->any())
        ->method('getUrl')
        ->willReturn($url);

        $this->registryMock->expects($this->once())
        ->method('registry')
        ->with('current_log')
        ->willReturn($returnLogMock);

        $this->assertEquals($returnLogMock, $this->blockMock->getLog());
    }
}