<?php
/*
 *
 * ClassyLlama_AvaTax
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
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Tax\Get;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ResponseTest
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response
 * @package ClassyLlama\AvaTax\Framework\Interaction\Tax\Get
 */
class ResponseTest extends TestCase
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response
     */
    private $testObject;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxResponseInterface = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::class,
            [

            ]
        );
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::getIsUnbalanced
     */
    public function testGetIsUnbalanced()
    {
        $this->dataObject
            ->expects($this->any())
            ->method('getData')
            ->willReturn("is_unbalanced");
        $this->testObject->getIsUnbalanced();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::setIsUnbalanced
     */
    public function testSetIsUnbalanced()
    {
        $this->dataObject
            ->expects($this->any())
            ->method('setData')
            ->with("is_unbalanced")
            ->willReturn($this->dataObject);
        $unbalanced = true;
        $this->testObject->setIsUnbalanced($unbalanced);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::getBaseAvataxTaxAmount
     */
    public function testGetBaseAvataxTaxAmount()
    {
        $this->dataObject
            ->expects($this->any())
            ->method('getData')
            ->willReturn("base_avatax_tax_amount");
        $this->testObject->getBaseAvataxTaxAmount();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response::setBaseAvataxTaxAmount
     */
    public function testSetBaseAvataxTaxAmount()
    {
        $this->dataObject
            ->expects($this->any())
            ->method('setData')
            ->with("base_avatax_tax_amount")
            ->willReturn($this->dataObject);
        $amount = 10.50;
        $this->testObject->setBaseAvataxTaxAmount($amount);
    }
}
