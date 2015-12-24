<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

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
 * @method Queue setEntityId() setEntityId(int $entityId)
 * @method Queue setQueueStatus() setQueueStatus(int $queueStatus)
 * @method Queue setAttempts() setAttempts(int $attempts)
 * @method Queue setMessage() setMessage(int $message)
 * @method ResourceModel\Queue\Collection getCollection()
 */
class Queue extends AbstractModel
{
    const ENTITY_TYPE_CODE_INVOICE = 'invoice';
    const ENTITY_TYPE_CODE_CREDITMEMO = 'creditmemo';
    const ENTITY_TYPE_CODE_ORDER = 'order';

    const QUEUE_STATUS_PENDING = 'pending';
    const QUEUE_STATUS_PROCESSING = 'processing';
    const QUEUE_STATUS_COMPLETE = 'complete';
    const QUEUE_STATUS_FAILED = 'failed';

    /**
     * @var Queue
     */
    protected $processing;

    /**
     * Queue constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Queue\Processing $processing
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Queue\Processing $processing,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->processing = $processing;
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
}
