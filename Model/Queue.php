<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Queue
 *
 * @method string getCreatedAt() getCreatedAt()
 * @method string getUpdatedAt() getUpdatedAt()
 * @method int getStoreId() getStoreId()
 * @method int getEntityTypeId() getEntityTypeId()
 * @method string getEntityTypeCode() getEntityTypeCode()
 * @method int getEntityId() getEntityId()
 * @method string getIncrementId() getIncrementId()
 * @method string getQueueStatus() getQueueStatus()
 * @method int getAttempts() getAttempts()
 * @method string getMessage() getMessage()
 * @method Queue setCreatedAt() setCreatedAt(string $createDateTime)
 * @method Queue setUpdatedAt() setUpdatedAt(string $updateDateTime)
 * @method Queue setStoreId() setStoreId(int $storeId)
 * @method Queue setEntityTypeId() setEntityTypeId(int $entityTypeId)
 * @method Queue setEntityTypeCode() setEntityTypeCode(string $entityTypeCode)
 * @method Queue setEntityId() setEntityId(int $entityId)
 * @method Queue setIncrementId() setIncrementId(string $incrementId)
 * @method Queue setQueueStatus() setQueueStatus(string $queueStatus)
 * @method Queue setAttempts() setAttempts(int $attempts)
 * @method Queue setMessage() setMessage(string $message)
 * @method ResourceModel\Queue\Collection getCollection()
 */
class Queue extends AbstractModel
{
    /**#@+
     * Entity Type Codes
     */
    const ENTITY_TYPE_CODE_INVOICE = 'invoice';
    const ENTITY_TYPE_CODE_CREDITMEMO = 'creditmemo';
    const ENTITY_TYPE_CODE_ORDER = 'order';
    /**#@-*/

    /**#@+
     * Queue Status Types
     */
    const QUEUE_STATUS_PENDING = 'pending';
    const QUEUE_STATUS_PROCESSING = 'processing';
    const QUEUE_STATUS_COMPLETE = 'complete';
    const QUEUE_STATUS_FAILED = 'failed';
    /**#@-*/

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Queue
     */
    protected $processing;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Queue constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AvaTaxLogger $avaTaxLogger
     * @param Queue\Processing $processing
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AvaTaxLogger $avaTaxLogger,
        Queue\Processing $processing,
        EavConfig $eavConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->processing = $processing;
        $this->eavConfig = $eavConfig;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Queue');
    }

    /*
     * Process this queued entity
     */
    public function process()
    {
        $this->processing->execute($this);
    }

    /**
     * @param int $storeId
     * @param string $entityTypeCode
     * @param int $entityId
     * @param string $incrementId
     * @param string $queueStatus
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build($storeId, $entityTypeCode, $entityId, $incrementId, $queueStatus)
    {
        // validating $entityTypeCode
        if (!in_array($entityTypeCode, [self::ENTITY_TYPE_CODE_INVOICE, self::ENTITY_TYPE_CODE_CREDITMEMO])) {
            $message = __('When building a queue record an invalid entity_type_code was provided');

            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                    'invalid_entity_type_code' => $entityTypeCode,
                ]
            );

            throw new \Exception($message);
        }

        // Get Entity Type Details
        $entityType = $this->eavConfig->getEntityType($entityTypeCode);

        $this->setStoreId($storeId);
        $this->setEntityTypeId($entityType->getEntityTypeId());
        $this->setEntityTypeCode($entityTypeCode);
        $this->setEntityId($entityId);
        $this->setIncrementId($incrementId);
        $this->setQueueStatus($queueStatus);
        $this->setAttempts(0);
    }
}
