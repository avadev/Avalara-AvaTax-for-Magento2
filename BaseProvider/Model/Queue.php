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
namespace ClassyLlama\AvaTax\BaseProvider\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface;

/**
 * Queue Model
 */
class Queue extends AbstractExtensibleModel implements QueueInterface
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue::class);
    }

    /**
     * @inheritDoc
     */
    public function getId(){
        return $this->getData(self::JOB_ID);
    }

    /**
     * @inheritDoc
     */
    public function getClient()
    {
        return $this->getData(self::CLIENT);
    }

    /**
     * @inheritDoc
     */
    public function getPayload()
    {
        return $this->getData(self::PAYLOAD);
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getAttempt()
    {
        return $this->getData(self::ATTEMPT);
    }

    /**
     * @inheritDoc
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::JOB_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function setClient($client)
    {
        return $this->setData(self::CLIENT, $client);
    }

    /**
     * @inheritDoc
     */
    public function setPayload($payload)
    {
        return $this->setData(self::PAYLOAD, $payload);
    }

    /**
     * @inheritDoc
     */
    public function setResponse($response)
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function setAttempt($attempt)
    {
        return $this->setData(self::ATTEMPT, $attempt);
    }

    /**
     * @inheritDoc
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
         return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
