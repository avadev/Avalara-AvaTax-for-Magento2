<?php
namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\Request
 */
class RequestTest extends TestCase
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
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Request\Request
     */
    private $testObject;

    /**
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->data = array();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Framework\Interaction\Request\Request::class,
            [
                'data' => $this->data,
            ]
        );
    }

    
    public function testClone()
    {
        $item = $this->testObject->__clone();
        $this->assertNull($item);

    }

}