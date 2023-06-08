<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class BatchQueueTransaction
 *
 * @package ClassyLlama\AvaTax\Model
 */
class BatchQueueTransaction extends AbstractModel implements BatchQueueTransactionInterface
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\BatchQueueTransaction::class);
    }

    /**
     * @return int
     */
    public function getBatchId(): int
    {
        return (integer) $this->getData(self::BATCH_ID);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return (integer)$this->getData(self::COMPANY_ID);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string) $this->getData(self::STATUS);
    }

    /**
     * @return int
     */
    public function getRecordCount(): int
    {
        return (integer) $this->getData(self::RECORD_COUNT);
    }

    /**
     * @return int
     */
    public function getInputFileId(): int
    {
        return (integer) $this->getData(self::INPUT_FILE_ID);
    }

    /**
     * @return int
     */
    public function getResultFileId(): int
    {
        return (integer) $this->getData(self::RESULT_FILE_ID);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @param int $batchId
     * @return BatchQueueTransactionInterface
     */
    public function setBatchId(int $batchId): BatchQueueTransactionInterface
    {
        return $this->setData(self::BATCH_ID, $batchId);
    }

    /**
     * @param string $name
     * @return BatchQueueTransactionInterface
     */
    public function setName(string $name): BatchQueueTransactionInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @param int $companyId
     * @return BatchQueueTransactionInterface
     */
    public function setCompanyId(int $companyId): BatchQueueTransactionInterface
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @param string $status
     * @return BatchQueueTransactionInterface
     */
    public function setStatus(string $status): BatchQueueTransactionInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @param int $recordsCount
     * @return BatchQueueTransactionInterface
     */
    public function setRecordCount(int $recordsCount): BatchQueueTransactionInterface
    {
        return $this->setData(self::RECORD_COUNT, $recordsCount);
    }

    /**
     * @param int $inputFileId
     * @return BatchQueueTransactionInterface
     */
    public function setInputFileId(int $inputFileId): BatchQueueTransactionInterface
    {
        return $this->setData(self::INPUT_FILE_ID, $inputFileId);
    }

    /**
     * @param int $resultFileId
     * @return BatchQueueTransactionInterface
     */
    public function setResultFileId(int $resultFileId): BatchQueueTransactionInterface
    {
        return $this->setData(self::RESULT_FILE_ID, $resultFileId);
    }
}
