<?php
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CustomsConfigTest
 * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class CustomsConfigTest extends TestCase
{
    const SCOPE_STORE   = 'store';
    const STORE_CODE = "default";
    const XML_PATH_AVATAX_CUSTOMS_ENABLED = 'tax/avatax_customs/enabled';
    const PRODUCT_ATTR_CROSS_BORDER_TYPE = 'avatax_cross_border_type';
    const XML_PATH_AVATAX_CUSTOMS_GROUND_SHIPPING_METHODS = 'tax/avatax_customs/ground_shipping_methods';
    const XML_PATH_AVATAX_CUSTOMS_OCEAN_SHIPPING_METHODS = 'tax/avatax_customs/ocean_shipping_methods';
    const XML_PATH_AVATAX_CUSTOMS_AIR_SHIPPING_METHODS = 'tax/avatax_customs/air_shipping_methods';
    const XML_PATH_AVATAX_CUSTOMS_CUSTOM_SHIPPING_METHODS_MAP = 'tax/avatax_customs/custom_shipping_methods_map';
    const XML_PATH_AVATAX_CUSTOMS_DEFAULT_SHIPPING_MODE = 'tax/avatax_customs/default_shipping_mode';
    const XML_PATH_AVATAX_DEFAULT_BORDER_TYPE = 'tax/avatax_customs/default_border_type';    
    const CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE = 'override_importer_of_record';
    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_DEFAULT = 'default';
    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_YES = "override_yes";
    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_NO = "override_no";
 
    protected $mainConfig;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);

        $this->config = $this->createMock(\ClassyLlama\AvaTax\Helper\Config::class);
        $this->crossBorder = $this->createMock(\ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass::class);
        
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);       
        
        $this->customsConfig = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Helper\CustomsConfig::class,
            [
                "context" => $this->context,
                "mainConfig" => $this->config,
                "crossBorderClassResource" => $this->crossBorder,
            ]
        );

        parent::setUp();
    }
    /**
     * tests if module is enabled
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::enabled
     */
    public function testEnabled()
    {
        $storecode = self::STORE_CODE;
        $string = 1;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_ENABLED, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertEquals(false, $this->customsConfig->enabled($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getGroundShippingMethods
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getGroundShippingMethods
     */
    public function testGetGroundShippingMethods()
    {
        $storecode = self::STORE_CODE;
        $string = "ground,flat";
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_GROUND_SHIPPING_METHODS, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsArray($this->customsConfig->getGroundShippingMethods($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getOceanShippingMethods
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getOceanShippingMethods
     */
    public function testGetOceanShippingMethods()
    {
        $storecode = self::STORE_CODE;
        $string = "ground,flat";
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_OCEAN_SHIPPING_METHODS, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsArray($this->customsConfig->getOceanShippingMethods($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getAirShippingMethods
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getAirShippingMethods
     */
    public function testGetAirShippingMethods()
    {
        $storecode = self::STORE_CODE;
        $string = "ground,flat";
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_AIR_SHIPPING_METHODS, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsArray($this->customsConfig->getAirShippingMethods($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getCustomShippingMethodMappings
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getCustomShippingMethodMappings
     */
    public function testGetCustomShippingMethodMappings()
    {
        $stub = array(
                '0' => array(
                        'custom_shipping_code_id' => '1',
                        'shipping_mode_id' => '1'
                        )
        );        
        $storecode = self::STORE_CODE;
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_CUSTOM_SHIPPING_METHODS_MAP, self::SCOPE_STORE)
            ->willReturn(json_encode($stub));
        $this->assertIsArray($this->customsConfig->getCustomShippingMethodMappings($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getDefaultShippingType
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getDefaultShippingType
     */
    public function testGetDefaultShippingType()
    {
        $storecode = self::STORE_CODE;
        $string = "ground";
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_CUSTOMS_DEFAULT_SHIPPING_MODE, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsString($this->customsConfig->getDefaultShippingType($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getUnitAmountAttributes
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getUnitAmountAttributes
     */
    public function testGetUnitAmountAttributes()
    {
        $storecode = self::STORE_CODE;
        $string = "ground";
        $this->crossBorder
            ->expects($this->any())
            ->method('getUnitAmountAttributes')
            ->willReturn('test');
        $this->assertIsString($this->customsConfig->getUnitAmountAttributes());
    }
    /**
     * tests if module is getDefaultBorderType
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getDefaultBorderType
     */
    public function testGetDefaultBorderType()
    {
        $storecode = self::STORE_CODE;
        $string = "flat";
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_AVATAX_DEFAULT_BORDER_TYPE, self::SCOPE_STORE)
            ->willReturn($string);
        $this->assertIsString($this->customsConfig->getDefaultBorderType($storecode, self::SCOPE_STORE));
    }
    /**
     * tests if module is getShippingTypeForMethod
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getShippingTypeForMethod
     */
    public function testGetShippingTypeForMethod()
    {
        $storecode = self::STORE_CODE;
        $scope = self::SCOPE_STORE;
        $method = "ground";
        
        $this->assertIsString($this->customsConfig->getShippingTypeForMethod($method, $storecode, $scope));
    }
    /**
     * tests if module is getShippingTypeForMethod
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CustomsConfig::getShippingTypeForMethod
     */
    public function testGetShippingTypeForMethod2()
    {
        $storecode = self::STORE_CODE;
        $scope = self::SCOPE_STORE;
        $method = "ground";
        
        $reflection = new \ReflectionClass(\ClassyLlama\AvaTax\Helper\CustomsConfig::class);
        $reflection_property = $reflection->getProperty('shippingMappings');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->customsConfig, [$method=>'test']);

        $this->assertIsString($this->customsConfig->getShippingTypeForMethod($method, $storecode, $scope));
    }
}
