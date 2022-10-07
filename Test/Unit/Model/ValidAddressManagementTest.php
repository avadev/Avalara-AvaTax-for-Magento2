<?php
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ClassyLlama\AvaTax\Api\ValidAddressManagementInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Customer\Api\Data\AddressInterface;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

/**
 * Class ValidAddressManagement
 * @covers \ClassyLlama\AvaTax\Model\ValidAddressManagement
 * @package ClassyLlama\AvaTax\Model
 */
class ValidAddressManagementTest extends TestCase
{
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->validationInteraction = $this->getMockBuilder(ValidationInteraction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeInterface);
        $this->storeInterface
            ->expects($this->any())
            ->method('getId')
            ->willReturn(0);
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Model\ValidAddressManagement::class,
            [
                'validationInteraction' => $this->validationInteraction,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ValidAddressManagement::saveValidAddress
     */
    public function testSaveValidAddress()
    {
        $this->testObject->saveValidAddress($this->address);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ValidAddressManagement::saveValidAddress
     */
    public function testSaveValidAddressWhenAvataxConnectionException()
    {
        $e = $this->objectManager->getObject(\ClassyLlama\AvaTax\Exception\AvataxConnectionException::class);
        $this->validationInteraction
            ->expects($this->any())
            ->method('validateAddress')
            ->willThrowException($e);
        $this->testObject->saveValidAddress($this->address);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\ValidAddressManagement::saveValidAddress
     */
    public function testSaveValidAddressWhenException()
    {
        $e = $this->objectManager->getObject(\Exception::class);
        $this->validationInteraction
            ->expects($this->any())
            ->method('validateAddress')
            ->willThrowException($e);
        $this->testObject->saveValidAddress($this->address);
    }
}
