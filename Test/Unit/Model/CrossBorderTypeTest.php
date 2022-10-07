<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CrossBorderType
 * @covers \ClassyLlama\AvaTax\Model\CrossBorderType
 * @package ClassyLlama\AvaTax\Model
 */
class CrossBorderTypeTest extends TestCase
{
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
		$this->CrossBorderTypeModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\CrossBorderType::class,
            []
        );
		parent::setUp();
    }

    /**
    * tests Initialize resource model
    * @test
    * @covers \ClassyLlama\AvaTax\Model\CrossBorderType::_construct
    */
    public function test()
    {
        $this->assertEquals($this->CrossBorderTypeModel, $this->CrossBorderTypeModel);
    }

    /**
    * @test
    * @covers \ClassyLlama\AvaTax\Model\CrossBorderType::getEntityId
    */
    public function testGetEntityId()
    {
        $this->CrossBorderTypeModel->getEntityId();
    }

    /**
     * test setEntityId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderType::setEntityId
     */
    public function testSetEntityId()
    {
        $data = '12345';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderType::class, $this->CrossBorderTypeModel->setEntityId($data));
    }

    /**
    * @test
    * @covers \ClassyLlama\AvaTax\Model\CrossBorderType::getType
    */
    public function testGetType()
    {
        $this->CrossBorderTypeModel->getType();
    }

    /**
     * test setType
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderType::setType
     */
    public function testSetType()
    {
        $data = 'abcde';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderType::class, $this->CrossBorderTypeModel->setType($data));
    }
}
