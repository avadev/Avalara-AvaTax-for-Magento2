<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\MetaData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectType
 */
class DataObjectTypeTest extends TestCase
{
    /**
     * Mock data
     *
     * @var \array|PHPUnit\Framework\MockObject\MockObject
     */
    private $data;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectType
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->data = [];
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\MetaData\DataObjectType::class,
            [
                'name' => '',
                'data' => $this->data,
            ]
        );
    }

    public function testSetOptions()
    {
        $validOptions = array();
        $result = $this->testObject->setOptions($validOptions);
        $this->assertFalse($result);
    }

    public function testSetSubtype()
    {
        $result = $this->testObject->setSubtype();
        $this->assertTrue($result);
    }

    public function testSetClass()
    {
        /*$class ="test";
        $result = $this->testObject->setClass($class);
        $this->assertTrue($result);*/
    }

    public function testValidateData()
    {
        $value = array("test");
        $result = $this->testObject->validateData($value);
        $this->assertNull($result);
    }

    public function testGetCacheKey()
    {
        $value = null;
        $cacheKey = $this->testObject->getCacheKey($value);
        $this->assertNotNull($cacheKey);
    }
}