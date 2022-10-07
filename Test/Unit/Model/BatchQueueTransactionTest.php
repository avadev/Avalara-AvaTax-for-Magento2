<?php

namespace ClassyLlama\AvaTax\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Model\Certificates;
use Magento\Backend\Model\Auth\Session as AuthSession;
use ClassyLlama\AvaTax\Helper\CertificateHelper;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject;

/**
 * Class BatchQueueTransactionTest
 * @package ClassyLlama\AvaTax\Test\Unit\Model
 */
class BatchQueueTransactionTest extends TestCase
{
    protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);

		 $this->batchQueueTransactionModel = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\BatchQueueTransaction::class,
            []
        );

       
		parent::setUp();
    }
    /**
    * tests Initialize resource model
    * @test
    * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::_construct
    */
    public function test()
    {
        $this->assertEquals($this->batchQueueTransactionModel, $this->batchQueueTransactionModel);
    }
    /**
     * test getBatchId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getBatchId
     */
    public function testGetBatchId()
    {
        $this->assertIsInt($this->batchQueueTransactionModel->getBatchId());
    }
    /**
     * test getName
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getName
     */
    public function testGetName()
    {
        $this->assertIsString($this->batchQueueTransactionModel->getName());
    }
    /**
     * test getCompanyId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getCompanyId
     */
    public function testGetCompanyId()
    {
        $this->assertIsInt($this->batchQueueTransactionModel->getCompanyId());
    }
    /**
     * test getStatus
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getStatus
     */
    public function testGetStatus()
    {
        $this->assertIsString($this->batchQueueTransactionModel->getStatus());
    }
    /**
     * test getRecordCount
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getRecordCount
     */
    public function testGetRecordCount()
    {
        $this->assertIsInt($this->batchQueueTransactionModel->getRecordCount());
    }
    /**
     * test getInputFileId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getInputFileId
     */
    public function testGetInputFileId()
    {
        $this->assertIsInt($this->batchQueueTransactionModel->getInputFileId());
    }
    /**
     * test getResultFileId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getResultFileId
     */
    public function testGetResultFileId()
    {
        $this->assertIsInt($this->batchQueueTransactionModel->getResultFileId());
    }
    /**
     * test getCreatedAt
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getCreatedAt
     */
    public function testGetCreatedAt()
    {
        $this->assertIsString($this->batchQueueTransactionModel->getCreatedAt());
    }
    /**
     * test getUpdatedAt
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::getUpdatedAt
     */
    public function testGetUpdatedAt()
    {
        $this->assertIsString($this->batchQueueTransactionModel->getUpdatedAt());
    }
    /**
     * test setBatchId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setBatchId
     */
    public function testSetBatchId()
    {
        $data = 12345;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setBatchId($data));
    }
    /**
     * test setName
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setName
     */
    public function testSetName()
    {
        $data = 'testname';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setName($data));
    }
    /**
     * test setCompanyId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setCompanyId
     */
    public function testSetCompanyId()
    {
        $data = 123;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setCompanyId($data));
    }
    /**
     * test setStatus
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setStatus
     */
    public function testSetStatus()
    {
        $data = 'approved';
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setStatus($data));
    }
    /**
     * test setRecordCount
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setRecordCount
     */
    public function testSetRecordCount()
    {
        $data = 123;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setRecordCount($data));
    }
    /**
     * test setInputFileId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setInputFileId
     */
    public function testSetInputFileId()
    {
        $data = 123;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setInputFileId($data));
    }
    /**
     * test setResultFileId
     * @test
     * @covers \ClassyLlama\AvaTax\Model\BatchQueueTransaction::setResultFileId
     */
    public function testSetResultFileId()
    {
        $data = 123;
        $this->assertInstanceOf(\ClassyLlama\AvaTax\Model\BatchQueueTransaction::class, $this->batchQueueTransactionModel->setResultFileId($data));
    }
}
