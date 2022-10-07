<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Framework\AvalaraClientWrapper;
use ClassyLlama\AvaTax\Framework\AvalaraClientWrapperFactory;
use ClassyLlama\AvaTax\Framework\Constants;
use ClassyLlama\AvaTax\Helper\Config;

/**
 * Class ClientPoolTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Rest
 */
class ClientPoolTest extends TestCase
{
    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
       $this->objectManager = new ObjectManager($this);

       $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMode'])
            ->getMock();

       $this->avalaraClientWrapperFactory = $this->createMock(AvalaraClientWrapperFactory::class);
        
        
        $appName = 'Avalara';
        $appVersion = '1.1.1';
        $environment = 'sandbox';
        $machineName = '';
        $type = 'tax';
        $guzzleParams = [];
        $this->ClientPoolObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool::class,
            [
                'config' => $this->config,
                'avalaraClientWrapperFactory' => $this->avalaraClientWrapperFactory
            ]
        );
        parent::setUp();
    }    
    
    /**
     * tests getClientCacheKey
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool::getClientCacheKey
     */
    public function testGetClientCacheKey()
    {
        $className = get_class($this->ClientPoolObject); 
        $reflection = new \ReflectionClass($className);
        $Obj = $reflection->getMethod('getClientCacheKey');
        $Obj->setAccessible(true);
        $Obj->invoke($this->ClientPoolObject, true, 'store', 1);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetClient()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetClient
     */
    public function testGetClient(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }
}