<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\Checkout\AddressValidationLayoutProcessor
 */
class AddressValidationLayoutProcessorWithoutChoiceTest extends TestCase
{
    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Mock storeManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\Checkout\AddressValidationLayoutProcessor
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->config->expects($this->any())
                                ->method('isModuleEnabled')
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('isAddressValidationEnabled')
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('getAddressValidationInstructionsWithChoice')
                                ->willReturn("Suggested with Choice");
        $this->config->expects($this->any())
                                ->method('getAddressValidationInstructionsWithoutChoice')
                                ->willReturn("Suggested without Choice");
        $this->config->expects($this->any())
                                ->method('getAddressValidationErrorInstructions')
                                ->willReturn("Invalid Address");
        $this->config->expects($this->any())
                                ->method('allowUserToChooseAddress')
                                ->willReturn(false);
        $this->config->expects($this->any())
                                ->method('getAddressValidationCountriesEnabled')
                                ->willReturn(true);
        
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->storeMock->expects($this->any())
                                ->method('getCode')
                                ->willReturn('US');
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->storeManagerMock->expects($this->any())
                                ->method('getStore')
                                ->willReturn($this->storeMock);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Block\Checkout\AddressValidationLayoutProcessor::class,
            [
                'config' => $this->config,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * tests process
     * @test
     * @covers \ClassyLlama\AvaTax\Block\Checkout\AddressValidationLayoutProcessor::process
     */
    public function testProcessWithoutChoice()
    {
        $jsLayout = [];
        $this->assertIsArray($this->testObject->process($jsLayout));
    }
}
