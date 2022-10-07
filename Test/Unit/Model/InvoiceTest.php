<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Invoice
 * @covers \ClassyLlama\AvaTax\Model\Invoice
 * @package ClassyLlama\AvaTax\Model
 */
class InvoiceTest extends TestCase
{
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
		$this->InvoiceModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\Invoice::class,
            []
        );
		parent::setUp();
    }

    /**
    * tests Initialize resource model
    * @test
    * @covers \ClassyLlama\AvaTax\Model\Invoice::_construct
    */
    public function test()
    {
        $this->assertEquals($this->InvoiceModel, $this->InvoiceModel);
    }

    /**
    * @test
    * @covers \ClassyLlama\AvaTax\Model\Invoice::getIdentities
    */
    public function testGetIdentities()
    {
        $this->assertIsArray($this->InvoiceModel->getIdentities());
    }
}
