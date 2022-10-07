<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @covers \ClassyLlama\AvaTax\Block\Cart\CartTotalsProcessor
 */
class CartTotalsProcessorTest extends TestCase
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\Cart\CartTotalsProcessor
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->scopeConfig->expects($this->any())
                                ->method('getValue')
                                ->willReturn(true);
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Block\Cart\CartTotalsProcessor::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * tests process
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Cart\CartTotalsProcessor::process
     */
    public function testProcess()
    {
        $jsLayout = [];
        $jsLayout['components']['block-totals']['children']['tax']['config']['title'] = "Tax";
        $this->assertIsArray($this->testObject->process($jsLayout));
    }
}
