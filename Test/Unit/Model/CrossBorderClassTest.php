<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CrossBorderClass
 * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass
 * @package ClassyLlama\AvaTax\Model
 */
class CrossBorderClassTest extends TestCase
{
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
		$this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\CrossBorderClass::class,
            []
        );
		parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::_construct
     */
    public function test()
    {
        $this->assertEquals($this->testObject, $this->testObject);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getDestinationCountries
     */
    public function testGetDestinationCountries()
    {
        $this->testObject->getDestinationCountries();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setDestinationCountries
     */
    public function testSetDestinationCountries()
    {
        $data = ['US','CA','UK'];
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setDestinationCountries($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getCrossBorderTypeId
     */
    public function testGetCrossBorderTypeId()
    {
        $this->testObject->getCrossBorderTypeId();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setCrossBorderTypeId
     */
    public function testSetCrossBorderTypeId()
    {
        $data = 1;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setCrossBorderTypeId($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getHsCode
     */
    public function testGetHsCode()
    {
        $this->testObject->getHsCode();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setHsCode
     */
    public function testSetHsCode()
    {
        $data = 'P000000';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setHsCode($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getUnitName
     */
    public function testGetUnitName()
    {
        $this->testObject->getUnitName();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setUnitName
     */
    public function testSetUnitName()
    {
        $data = 'abcde';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setUnitName($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getUnitAmountAttrCode
     */
    public function testGetUnitAmountAttrCode()
    {
        $this->testObject->getUnitAmountAttrCode();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setUnitAmountAttrCode
     */
    public function testSetUnitAmountAttrCode()
    {
        $data = 'test_code';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setUnitAmountAttrCode($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::getPrefProgramIndicator
     */
    public function testGetPrefProgramIndicator()
    {
        $this->testObject->getPrefProgramIndicator();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\CrossBorderClass::setPrefProgramIndicator
     */
    public function testSetPrefProgramIndicator()
    {
        $data = 'abcd';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, $this->testObject->setPrefProgramIndicator($data));
    }
}
