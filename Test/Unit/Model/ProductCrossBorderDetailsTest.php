<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductCrossBorderDetails
 * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails
 * @package ClassyLlama\AvaTax\Model
 */
class ProductCrossBorderDetailsTest extends TestCase
{
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
		$this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class,
            []
        );
		parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getProductId
     */
    public function testGetProductId()
    {
        $this->testObject->getProductId();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setProductId
     */
    public function testSetProductId()
    {
        $data = 1;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setProductId($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getDestinationCountry
     */
    public function testGetDestinationCountry()
    {
        $this->testObject->getDestinationCountry();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setDestinationCountry
     */
    public function testSetDestinationCountry()
    {
        $data = 'US';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setDestinationCountry($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getHsCode
     */
    public function testGetHsCode()
    {
        $this->testObject->getHsCode();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setHsCode
     */
    public function testSetHsCode()
    {
        $data = 'P000000';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setHsCode($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getUnitName
     */
    public function testGetUnitName()
    {
        $this->testObject->getUnitName();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setUnitName
     */
    public function testSetUnitName()
    {
        $data = 'abcde';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setUnitName($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getUnitAmountAttrCode
     */
    public function testGetUnitAmountAttrCode()
    {
        $this->testObject->getUnitAmountAttrCode();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setUnitAmountAttrCode
     */
    public function testSetUnitAmountAttrCode()
    {
        $data = 'test_code';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setUnitAmountAttrCode($data));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::getPrefProgramIndicator
     */
    public function testGetPrefProgramIndicator()
    {
        $this->testObject->getPrefProgramIndicator();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::setPrefProgramIndicator
     */
    public function testSetPrefProgramIndicator()
    {
        $data = 'abcd';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\ProductCrossBorderDetails::class, $this->testObject->setPrefProgramIndicator($data));
    }
}
