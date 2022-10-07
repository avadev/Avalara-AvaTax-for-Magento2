<?php
/*
 * Avalara_BaseProvider
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
namespace ClassyLlama\AvaTax\BaseProvider\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Queue Job interface.
 * @api
 */
interface QueueInterface extends ExtensibleDataInterface
{
    const MIN_ATTEMPT = 0;
    const MAX_ATTEMPT = 3;

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const JOB_ID        = 'job_id';
    const CLIENT        = 'client';
    const PAYLOAD       = 'payload';
    const RESPONSE      = 'response';
    const STATUS        = 'status';
    const ATTEMPT       = 'attempt';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    /**#@-*/

    /**
     * Get Job ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get client
     *
     * @return string
     */
    public function getClient();

    /**
     * Get payload
     *
     * @return string
     */
    public function getPayload();

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse();


    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get attempt
     *
     * @return int
     */
    public function getAttempt();

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Set Job ID
     *
     * @param int $id
     * @return JobInterface
     */
    public function setId($id);

    /**
     * Set Client
     *
     * @param string $client
     * @return JobInterface
     */
    public function setClient($client);

    /**
     * Set Payload
     *
     * @param string $payload
     * @return JobInterface
     */
    public function setPayload($payload);

    /**
     * Set Response
     *
     * @param string $response
     * @return JobInterface
     */
    public function setResponse($response);

    /**
     * Set Status
     *
     * @param int $status
     * @return JobInterface
     */
    public function setStatus($status);

    /**
     * Set Attempt
     *
     * @param int $attempt
     * @return JobInterface
     */
    public function setAttempt($attempt);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return JobInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return JobInterface
     */
    public function setUpdateTime($updateTime);

    /**
      * @return \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueExtensionInterface
      */
      public function getExtensionAttributes();
 
      /**
       * @param \ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueExtensionInterface $extensionAttributes
       * @return void
       */
      public function setExtensionAttributes(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueExtensionInterface $extensionAttributes);
}
