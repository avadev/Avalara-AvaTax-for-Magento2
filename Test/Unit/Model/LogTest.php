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

namespace ClassyLlama\AvaTax\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LogTest
 * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Log
 * @package ClassyLlama\AvaTax\BaseProvider\Test\Unit\Model
 */
class LogTest extends TestCase
{
	protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);

		 $this->logModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Model\Log::class,
            []
        );

       
		parent::setUp();
    }

    /**
    * tests Initialize resource model
    * @test
    * @covers \ClassyLlama\AvaTax\BaseProvider\Model\Log::_construct
    */
    public function test()
    {

        $this->assertEquals($this->logModel, $this->logModel);
    }
}