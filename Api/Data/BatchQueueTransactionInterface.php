<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Api\Data;

/**
 * Interface BatchQueueTransactionInterface
 *
 * @package ClassyLlama\AvaTax\Api\Data
 */
interface BatchQueueTransactionInterface
{
    const WAITING_STATUS = "Waiting";
    const COMPLETED_STATUS = "Completed";
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const BATCH_ID = 'batch_id';
    const NAME = 'name';
    const COMPANY_ID = 'company_id';
    const STATUS = 'status';
    const RECORD_COUNT = 'record_count';
    const INPUT_FILE_ID = 'input_file_id';
    const RESULT_FILE_ID = 'result_file_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * @return int
     */
    public function getBatchId(): int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getCompanyId(): int;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return int
     */
    public function getRecordCount(): int;

    /**
     * @return int
     */
    public function getInputFileId(): int;

    /**
     * @return int
     */
    public function getResultFileId(): int;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param int $batchId
     * @return BatchQueueTransactionInterface
     */
    public function setBatchId(int $batchId): BatchQueueTransactionInterface;

    /**
     * @param string $name
     * @return BatchQueueTransactionInterface
     */
    public function setName(string $name): BatchQueueTransactionInterface;

    /**
     * @param int $companyId
     * @return BatchQueueTransactionInterface
     */
    public function setCompanyId(int $companyId): BatchQueueTransactionInterface;

    /**
     * @param string $status
     * @return BatchQueueTransactionInterface
     */
    public function setStatus(string $status): BatchQueueTransactionInterface;

    /**
     * @param int $recordsCount
     * @return BatchQueueTransactionInterface
     */
    public function setRecordCount(int $recordsCount): BatchQueueTransactionInterface;

    /**
     * @param int $inputFileId
     * @return BatchQueueTransactionInterface
     */
    public function setInputFileId(int $inputFileId): BatchQueueTransactionInterface;

    /**
     * @param int $resultFileId
     * @return BatchQueueTransactionInterface
     */
    public function setResultFileId(int $resultFileId): BatchQueueTransactionInterface;

}
