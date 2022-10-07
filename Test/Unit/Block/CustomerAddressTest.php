<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\CustomerAddress
 */
class CustomerAddressTest extends TestCase
{
    /**
     * Mock context
     *
     * @var \Magento\Framework\View\Element\Template\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Mock config
     *
     * @var \ClassyLlama\AvaTax\Helper\Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\CustomerAddress
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
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
        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->urlMock->expects($this->any())
                                ->method('getUrl')
                                ->willReturn("https://www.avalara.com/");
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->context->expects($this->any())
                                ->method('getStoreManager')
                                ->willReturn($this->storeManagerMock);
        $this->context->expects($this->any())
                                ->method('getUrlBuilder')
                                ->willReturn($this->urlMock);
                                
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
                                ->willReturn(true);
        $this->config->expects($this->any())
                                ->method('getAddressValidationCountriesEnabled')
                                ->willReturn(true);

        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Block\CustomerAddress::class,
            [
                'context' => $this->context,
                'config'  => $this->config,
                'data'    => []
            ]
        );
    }

    /**
     * tests getStoreCode
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getStoreCode
     */
    public function testGetStoreCode()
    {
        $this->assertIsString($this->testObject->getStoreCode());
    }

    /**
     * tests isValidationEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::isValidationEnabled
     */
    public function testIsValidationEnabled()
    {
        $this->assertIsBool($this->testObject->isValidationEnabled());
    }

    /**
     * tests getChoice
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getChoice
     */
    public function testGetChoice()
    {
        $this->assertIsBool($this->testObject->getChoice());
    }

    /**
     * tests getInstructions
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getInstructions
     */
    public function testGetInstructionsWithChoice()
    {
        $this->assertIsString($this->testObject->getInstructions());
    }

    /**
     * tests getErrorInstructions
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getErrorInstructions
     */
    public function testGetErrorInstructions()
    {
        $this->assertIsString($this->testObject->getErrorInstructions());
    }

    /**
     * tests getCountriesEnabled
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getCountriesEnabled
     */
    public function testGetCountriesEnabled()
    {
        $this->assertIsBool($this->testObject->getCountriesEnabled());
    }

    /**
     * tests getBaseUrl
     * @test
     * @covers \ClassyLlama\AvaTax\Block\CustomerAddress::getBaseUrl
     */
    public function testGetBaseUrl()
    {
        $this->assertIsString($this->testObject->getBaseUrl());
    }

}
