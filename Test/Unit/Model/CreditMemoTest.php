<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CreditMemo
 * @covers \ClassyLlama\AvaTax\Model\CreditMemo
 * @package ClassyLlama\AvaTax\Model
 */
class CreditMemoTest extends TestCase
{
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
		$this->creditMemoModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\CreditMemo::class,
            []
        );
		parent::setUp();
    }

    /**
    * tests Initialize resource model
    * @test
    * @covers \ClassyLlama\AvaTax\Model\CreditMemo::_construct
    */
    public function test()
    {
        $this->assertEquals($this->creditMemoModel, $this->creditMemoModel);
    }

    /**
    * @test
    * @covers \ClassyLlama\AvaTax\Model\CreditMemo::getIdentities
    */
    public function testGetIdentities()
    {
        $this->assertIsArray($this->creditMemoModel->getIdentities());
    }
}
