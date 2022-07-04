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
use TestDeferred\TestClass;

/**
 * Class ClearButtonTest
 * @covers ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\ClearButton
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log
 */

class ClearButtonTest extends TestCase
{
    /**
     * Setup
     * @covers ClassyLlama\AvaTax\BaseProvider\Test\Unit\Block\Adminhtml\Log\ClearButton
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
    	$this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Widget\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
        ->setMethods(['getUrl'])
        ->getMock();

        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->block = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\ClearButton::class,
            [
                "context" => $this->context
            ]
        );

    	parent::setUp();

    }

    /**
     * tests button data
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\ClearButton::getButtonData
     */
    public function testGetButtonData()
    {
        $url = "";
        $message = __(
            'This will clear any logs that are older than the lifetime set in configuration. ' .
            'Do you want to continue?'
        );
        $return  = [
            'label' => __('Clear Logs Now'),
            'on_click' => "confirmSetLocation('{$message}', '{$url}')"
        ];

        $this->assertEquals($return, $this->block->getButtonData());
    }
    
    /**
     * tests button url
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Block\Adminhtml\Log\ClearButton::getButtonUrl
     */
    public function testGetButtonUrl()
    {
        $url = "https://localhost/test/new/clear";
        
        $this->urlBuilderMock->expects($this->any())
        ->method('getUrl')
        ->willReturn($url);

        $className = get_class($this->block);
        $reflection = new \ReflectionClass($className);
        $getButtonUrl = $reflection->getMethod('getButtonUrl');
        $getButtonUrl->setAccessible(true);

        $this->assertEquals($url, $getButtonUrl->invoke($this->block));
    }
}