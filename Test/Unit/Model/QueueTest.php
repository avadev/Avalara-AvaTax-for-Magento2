<?php
/*
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
namespace ClassyLlama\AvaTax\Test\Unit\Model;

use ClassyLlama\AvaTax\BaseProvider\Model\Queue;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Queue
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model
 */
class QueueTest extends TestCase
{
	const JOB_ID		= '1';
	const CLIENT		= 'avalara';
	const PAYLOAD		= '/v3/calculations';
	const RESPONSE		= 'response';
	const STATUS		= 'pass';
	const ATTEMPT		= '1';
	const CREATION_TIME	= '1';
	const UPDATE_TIME	= '1';
	/**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;
	/**
     * @var Queue
     */
    protected $queue;
	
	/**
     * Setup method for creating necessary objects
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $arguments = $this->objectManagerHelper->getConstructArguments(
            Queue::class,
            []
        );
        $this->queue = $this->objectManagerHelper->getObject(Queue::class, $arguments);
    }
	
	/**
     * Test getId()
     *
     * @return null
     */
    public function testGetId()
    {
        $this->assertNull($this->queue->getId());
    }
	/**
     * Test getClient()
     *
     * @return null
     */
    public function testGetClient()
    {
        $this->assertNull($this->queue->getClient());
    }

    /**
     * Test getPayload()
     *
     * @return null
     */
    public function testGetPayload()
    {
        $this->assertNull($this->queue->getPayload());
    }

    /**
     * Test getResponse()
     *
     * @return null
     */
    public function testGetResponse()
    {
        $this->assertNull($this->queue->getResponse());
    }

    /**
     * Test getStatus()
     *
     * @return null
     */
    public function testGetStatus()
    {
        $this->assertNull($this->queue->getStatus());
    }

    /**
     * Test getAttempt()
     *
     * @return null
     */
    public function testGetAttempt()
    {
        $this->assertNull($this->queue->getAttempt());
    }

    /**
     * Test getCreationTime()
     *
     * @return null
     */
    public function testGetCreationTime()
    {
        $this->assertNull($this->queue->getCreationTime());
    }

    /**
     * Test getUpdateTime()
     *
     * @return null
     */
    public function testGetUpdateTime()
    {
        $this->assertNull($this->queue->getUpdateTime());
    }
	/**
     * Test setId()
     *
     * @return not null
     */
    public function testSetId()
    {
		$id = self::JOB_ID;
        $this->assertNotNull($this->queue->setId($id));
    }
	/**
     * Test setClient()
     *
     * @return not null
     */
    public function testSetClient()
    {
		$client = self::CLIENT;
        $this->assertNotNull($this->queue->setClient($client));
    }
	/**
     * Test setPayload()
     *
     * @return not null
     */
    public function testSetPayload()
    {
		$payload = self::PAYLOAD;
        $this->assertNotNull($this->queue->setPayload($payload));
    }
	/**
     * Test setResponse()
     *
     * @return not null
     */
    public function testSetResponse()
    {
		$response = self::RESPONSE;
        $this->assertNotNull($this->queue->setResponse($response));
    }
	/**
     * Test setStatus()
     *
     * @return not null
     */
    public function testSetStatus()
    {
		$status = self::STATUS;
        $this->assertNotNull($this->queue->setStatus($status));
    }
	/**
     * Test setAttempt()
     *
     * @return not null
     */
    public function testSetAttempt()
    {
		$attempt = self::ATTEMPT;
        $this->assertNotNull($this->queue->setAttempt($attempt));
    }
	/**
     * Test setCreationTime()
     *
     * @return not null
     */
    public function testSetCreationTime()
    {
		$creation_time = self::CREATION_TIME;
        $this->assertNotNull($this->queue->setCreationTime($creation_time));
    }
	/**
     * Test setUpdateTime()
     *
     * @return not null
     */
    public function testSetUpdateTime()
    {
		$update_time = self::UPDATE_TIME;
        $this->assertNotNull($this->queue->setUpdateTime($update_time));
    }
}