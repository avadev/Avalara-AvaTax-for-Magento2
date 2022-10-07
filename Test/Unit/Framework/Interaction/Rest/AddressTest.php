<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Api\RestAddressInterface;
use ClassyLlama\AvaTax\Helper\AvaTaxClientWrapperFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result as AddressResult;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\ResultFactory as AddressResultFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Helper\Config as ConfigHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\CacheInterface;
use Zend\Serializer\Adapter\PhpSerialize;
use Psr\Http\Message\RequestInterface;

/**
 * Class AddressTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest
 */
class AddressTest extends TestCase
{
    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
       $this->objectManager = new ObjectManager($this);

       $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMode'])
            ->getMock();    

       $this->phpSerialize = $this->createMock(PhpSerialize::class);

       $this->cache = $this->createMock(CacheInterface::class);

       $this->metaDataObject = $this->getMockBuilder(MetaDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCacheKeyFromObject'])
            ->getMock(); ;

       $this->restConfig = $this->createMock(RestConfig::class);

       $this->addressResultFactory = $this->createMock(AddressResultFactory::class);

       $this->addressResult = $this->createMock(AddressResult::class);

       $this->rest = $this->createMock(Rest::class);

       $this->clientFactoryMock = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\AvaTaxClientWrapperFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create','withCatchExceptions','withBasicToken','createTaxTransaction','ping','authenticated','resolveAddress'])
                ->getMock();

        $this->AddressObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address::class,
            [
                'phpSerialize' => $this->phpSerialize,
                'cache' => $this->cache,
                'metaDataObject' => $this->metaDataObject,
                'restConfig' => $this->restConfig,
                'addressResultFactory' => $this->addressResultFactory,
                'rest' => $this->rest,
                'clientFactory' => $this->clientFactoryMock
            ]
        );
        parent::setUp();
    }    
    
    /**
     * tests validate
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address::validate
     */
    public function testValidateResultTest()
    {
        $cache =  new \Magento\Framework\DataObject();
        $data = new \Magento\Framework\DataObject();
        $data->setAddress($cache);

        $this->metaDataObject
         ->method('getCacheKeyFromObject')
         ->with($cache)
         ->willReturn('Test');

        $this->cache
         ->method('load')
         ->with('Test')
         ->willReturn(json_encode([$this->addressResult]));

        $this->phpSerialize
         ->method('unserialize')
         ->with(json_encode([$this->addressResult]))
         ->willReturn([$this->addressResult]);

        //$this->assertEquals($this->addressResult, $this->AddressObject->validate($data));
    }

    /**
     * tests validateResult
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address::validateResult
     */
    public function testValidateResultWarning()
    {   
        $messages =  new \Magento\Framework\DataObject();
        $messages->severity = 'severityTest';
        $messages->summary = 'summaryTest';

        $resultData =  new \Magento\Framework\DataObject();
        $resultData->messages = [$messages];

         $this->restConfig
         ->method('getErrorSeverityLevels')
         ->willReturn(['severity']);

        $this->restConfig
         ->method('getWarningSeverityLevels')
         ->willReturn(['severityTest']);
        
        $className = get_class($this->AddressObject); 
        $reflection = new \ReflectionClass($className);
        $result = $reflection->getMethod('validateResult');
        $result->setAccessible(true);
        $result->invoke($this->AddressObject, $resultData);
    }
}