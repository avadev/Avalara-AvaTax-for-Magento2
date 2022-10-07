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
namespace ClassyLlama\AvaTax\Test\Unit\Model\Config\Source\Queue;

use ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue\Status
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model\Config\Source\Queue
 */
class StatusTest extends TestCase
{
	/**
     * Setup method for creating necessary objects
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $arguments = $this->objectManagerHelper->getConstructArguments(
            Status::class,
            []
        );
        $this->status = $this->objectManagerHelper->getObject(Status::class, $arguments);
    }
	/**
     * Test toOptionArray()
     *
     * @return array
     */
	public function testToOptionArray()
    {
        $this->assertIsArray($this->status->toOptionArray());
    }
}