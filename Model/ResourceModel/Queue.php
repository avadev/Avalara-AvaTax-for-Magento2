<?php

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Queue
 */
class Queue extends AbstractDb
{
    const QUEUE_STATUS_FIELD_NAME = 'queue_status';
    const UPDATED_AT_FIELD_NAME = 'updated_at';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_queue', 'queue_id');
    }

    /**
     * Set date of last update
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var $object \ClassyLlama\AvaTax\Model\Queue */
        $date = $this->dateTime->gmtDate();
        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($date);
        } else {
            $object->setUpdatedAt($date);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Update the status of a queue record and check to confirm the exclusive change
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function changeQueueStatusWithLocking(\Magento\Framework\Model\AbstractModel $object) {

        /* @var $object \ClassyLlama\AvaTax\Model\Queue */
        $object->setUpdatedAt($this->dateTime->gmtDate());
        $data = $this->prepareDataForUpdate($object);

        $originalQueueStatus = $object->getOrigData(self::QUEUE_STATUS_FIELD_NAME);
        $originalUpdatedAt = $object->getOrigData(self::UPDATED_AT_FIELD_NAME);

        // A conditional update does a read lock on update so we use the condition on the old
        // queue status here to guarantee that nothing else has modified the status for processing
        $condition = array();

        // update only the queue record identified by Id
        $condition[] = $this->getConnection()->quoteInto($this->getIdFieldName() . '=?', $object->getId());

        // only update the record if it is still pending
        $condition[] = $this->getConnection()->quoteInto(self::QUEUE_STATUS_FIELD_NAME . '=?', $originalQueueStatus);

        // only update the record if nothing else has updated it
        $condition[] = $this->getConnection()->quoteInto(self::UPDATED_AT_FIELD_NAME . '=?', $originalUpdatedAt);

        // update the record and get the number of affected records
        $affectedRowCount = $this->getConnection()->update($this->getMainTable(), $data, $condition);

        $result = false;
        if ($affectedRowCount > 0) {
            $object->setHasDataChanges(false);
            $result = true;
        }

        return $result;
    }
}
