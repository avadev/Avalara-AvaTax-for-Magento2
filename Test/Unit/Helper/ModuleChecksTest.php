<?php
/*
 *
 * Avalara_BrSalesTax
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
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleChecksTest
 * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class ModuleChecksTest extends TestCase
{
    protected $objectManagerHelper;
    protected $context;
    protected $controller;
    protected $resultFactoryMock;
    protected $resultMock;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [ 'getDefaultStoreView' ]
            )
            ->getMockForAbstractClass();
        
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->taxRuleRepositoryMock = $this->getMockBuilder(\Magento\Tax\Api\TaxRuleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'get', 'delete', 'deleteById', 'getList'])
            ->getMockForAbstractClass();
        $this->_searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
                ->disableOriginalConstructor()
                ->setMethods(['addFilter','create','setPageSize','setSortOrders'])->getMock();
        $this->searchCriteriaObject = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->_searchCriteriaBuilder->method('create')->willReturn($this->searchCriteriaObject);
        $this->searchCriteriaInterfaceMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMockForAbstractClass();
        $this->searchCriteriaResult = $this->getMockBuilder(\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        $taxRules = ["rule_1","rule_2"];
        $this->searchCriteriaResult->expects($this->any())
                ->method('getItems')
                ->willReturn($taxRules);
        $this->taxRuleRepositoryMock->expects($this->any())
                ->method('getList')
                ->willReturn($this->searchCriteriaResult);
        
        $this->avaTaxConfigMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'isModuleEnabled',
                    'getTaxMode',
                    'isNativeTaxRulesIgnored'
                ]
            )
            ->getMock();
        $this->backendUrlMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        $this->helper = $this->objectManagerHelper->getObject(
            \ClassyLlama\AvaTax\Helper\ModuleChecks::class,
            [
                'context' => $this->context,
                'storeManager' => $this->storeManagerMock,
                'taxRuleRepository' => $this->taxRuleRepositoryMock,
                'searchCriteriaBuilder' => $this->_searchCriteriaBuilder,
                'avaTaxConfig' => $this->avaTaxConfigMock,
                'backendUrl' => $this->backendUrlMock
            ]
        );
        parent::setUp();
    }
    
    /**
     * Get module check errors
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::getModuleCheckErrors
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkSslSupport
     */
    public function testGetModuleCheckErrors()
    {
        $errors = [];
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isNativeTaxRulesIgnored')
            ->willReturn(false);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('getTaxMode')
            ->with('1')
            ->willReturn(3);

            $this->helper->getModuleCheckErrors();
    }
    /**
     * Get module check errors
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkSslSupport
     */
    public function testCheckSslSupport()
    {
        $errors = [];
        $mockedInstance = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\ModuleChecks::class)
        ->disableOriginalConstructor()
        ->getMock();
        $reflectedMethod = new \ReflectionMethod(
            \ClassyLlama\AvaTax\Helper\ModuleChecks::class,
            'checkSslSupport'
        );
        $reflectedMethod->setAccessible(true);
        $this->assertEquals($errors, $reflectedMethod->invokeArgs($mockedInstance, []));
    }
    /**
     * Get module check checkOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkOriginAddress
     */
    public function testCheckOriginAddress()
    {
        $errors = [];
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('getTaxMode')
            ->with('1')
            ->willReturn(1);
        
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID]
            )
            ->willReturn(false);
            
        $this->assertIsArray($this->helper->checkOriginAddress());
    }

    /**
     * Get module check checkOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkOriginAddress
     */
    public function testCheckOriginAddressWithError()
    {
        $errors = [];
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('getTaxMode')
            ->with('1')
            ->willReturn(3);
        
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID
            )
            ->willReturn(false);
            
        $this->assertIsArray($this->helper->checkOriginAddress());
    }

    /**
     * Get module check checkOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkOriginAddress
     */
    public function testCheckOriginAddressWithError2()
    {
        $errors = [];
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('getTaxMode')
            ->with('1')
            ->willReturn(3);

        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID, 'default', null, true],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID, 'default', null, false],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY, 'default', null, true],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE, 'default', null, true],
                ]
            ));
        
            
        $this->assertIsArray($this->helper->checkOriginAddress());
    }

    /**
     * Get module check checkOriginAddress
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\ModuleChecks::checkOriginAddress
     */
    public function testCheckOriginAddressWithError3()
    {
        $errors = [];
        $this->avaTaxConfigMock->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn(1);
        $this->avaTaxConfigMock->expects($this->any())
            ->method('getTaxMode')
            ->with('1')
            ->willReturn(3);
        
            $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID, 'default', null, true],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID, 'default', null, true],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY, 'default', null, false],
                    [\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE, 'default', null, true],
                ]
            ));
            
        $this->assertIsArray($this->helper->checkOriginAddress());
    }
}

function function_exists($mtethod)
{

}
