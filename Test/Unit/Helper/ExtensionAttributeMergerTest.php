<?php
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ExtensionAttributeMergerTest
 * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class ExtensionAttributeMergerTest extends TestCase
{
   
    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->joinProcessorHelper = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesFactory  = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
                 
        
        $this->ExtensionAttributeMerger = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class,
            [
                "joinProcessorHelper" => $this->joinProcessorHelper,
                "extensionAttributesFactory " => $this->extensionAttributesFactory
            ]
        );

        parent::setUp();
    }
    /**
     * getExtensionAttributeMethodName
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::getExtensionAttributeMethodName
     */
    public function testGetExtensionAttributeMethodName()
    {
        $key = 1;
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class);
        $method = $reflection->getMethod('getExtensionAttributeMethodName');
        $method->setAccessible(true);
        $this->assertIsString($method->invokeArgs($this->ExtensionAttributeMerger, [$key]));
    }

    /**
     * getExtensionAttribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::getExtensionAttribute
     */
    public function testGetExtensionAttribute()
    {
        $key = 'first_name';
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFirstName'])
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($extensionAttributes);
        $this->ExtensionAttributeMerger->getExtensionAttribute($extensionAttributes, $key);
    }

    /**
     * setExtensionAttribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::setExtensionAttribute
     */
    public function testSetExtensionAttribute()
    {
        $value = 'text';
        $key = 'first_name';
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFirstName'])
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($extensionAttributes);
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class);
        $method = $reflection->getMethod('setExtensionAttribute');
        $method->setAccessible(true);
        $this->assertIsObject($method->invokeArgs($this->ExtensionAttributeMerger, [$extensionAttributes, $key, $value]));
    }

    /**
     * canSetExtensionAttribute
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::canSetExtensionAttribute
     */
    public function testCanSetExtensionAttribute()
    {
        $value = null;
        $key = 'first_name';
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFirstName'])
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory->expects($this->any())->method('create')->willReturn($extensionAttributes);
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class);
        $method = $reflection->getMethod('canSetExtensionAttribute');
        $method->setAccessible(true);
        $this->assertIsBool($method->invokeArgs($this->ExtensionAttributeMerger, [$extensionAttributes, $key, $value]));
    }

    /**
     * copyAttributes
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::copyAttributes
     */
    public function testCopyAttributes()
    {
        $from = $this->getMockBuilder(\Magento\Framework\Model\AbstractExtensibleModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $to = $this->getMockBuilder(\Magento\Framework\Model\AbstractExtensibleModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $from
            ->expects($this->any())
            ->method('getData')
            ->with(\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY)
            ->willReturn('text1');
        $this->extensionAttributesFactory
            ->expects($this->any())
            ->method('getExtensibleInterfaceName')
            ->with(get_class($from))
            ->willReturn('text4');
        $whitelist = ['a' => 'text', 'b' => 'text2', 'c' => 'text3'];
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger::class);
        $method = $reflection->getMethod('copyAttributes');
        $method->setAccessible(true);
        $method->invokeArgs($this->ExtensionAttributeMerger, [$from, $to, $whitelist]);
    }
}
