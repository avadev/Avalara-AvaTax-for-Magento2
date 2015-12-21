<?php

namespace ClassyLlama\AvaTax\Model;

use Symfony\Component\Config\Definition\Exception\Exception;

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
 * @method Queue setUpdatedAt() setUpdatedAt(string $updateDateTime)
 * @method Queue setStoreId() setStoreId(int $storeId)
 * @method Queue setEntityTypeId() setEntityTypeId(int $entityTypeId)
 * @method Queue setEntityId() setEntityId(int $entityId)
 * @method Queue setQueueStatus() setQueueStatus(int $queueStatus)
 * @method Queue setAttempts() setAttempts(int $attempts)
 * @method Queue setMessage() setMessage(int $message)
 * @method ResourceModel\Queue\Collection getCollection()
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const ENTITY_TYPE_CODE_INVOICE = 'invoice';
    const ENTITY_TYPE_CODE_CREDITMEMO = 'creditmemo';

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface
     */
    protected $invoiceCommentRepository;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceCommentInterface
     */
    protected $invoiceCommentFactory;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceCommentInterfacePersistor
     */
    protected $invoiceCommentPersistor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface $invoiceComment
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterfacePersistor $invoiceCommentPersistor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $invoiceCommentRepository,
        // \Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory $invoiceCommentFactory, /* TODO: would prefer to use interface but no save method exists */
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Api\Data\InvoiceCommentInterfacePersistor $invoiceCommentPersistor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->invoiceManagement = $invoiceManagement;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceCommentFactory = $invoiceCommentFactory;
        $this->invoiceCommentPersistor = $invoiceCommentPersistor;
        $this->invoiceCommentRepository = $invoiceCommentRepository;
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
        // TODO: acquire lock on queue table
        // TODO: check to see if the queue record is pending
            // TODO: if no longer pending, log a warning
        // TODO: update queue record with new processing status
        // TODO: release lock on table

        // TODO: update queue incrementing attempts
        $this->setAttempts($this->getAttempts()+1)->save();

        // TODO: process entity with AvaTax

        // TODO: get entity
        // TODO: add comment to entity indicating what was done
        if ($this->getEntityTypeCode() === self::ENTITY_TYPE_CODE_INVOICE) {

            /* @var $invoice \Magento\Sales\Api\Data\InvoiceInterface */
            $invoice = $this->invoiceRepository->get($this->getEntityId());
            if ($invoice->getEntityId()) {

                $allComments = $this->invoiceManagement->getCommentsList($invoice->getEntityId());

                $commentFromFactory = $this->invoiceCommentFactory->create();
                $commentFromFactory->setParentId($invoice->getEntityId());
                $commentFromFactory->setComment('howdy partner');
                $commentFromFactory->setIsCustomerNotified(false);
                $commentFromFactory->setIsVisibleOnFront(false);

                // save the comment
                //$this->invoiceCommentRepository->save($comment_from_repository);
                $commentFromFactory->save();
            } else {
                throw new \Exception('Invoice not found: (EntityId: ' . $this->getEntityId() . ', IncrementId: ' . $this->getIncrementId() . ')');
            }

            $entity = '';

        } elseif ($this->getEntityTypeCode() === self::ENTITY_TYPE_CODE_CREDITMEMO) {
            $entity = '';

        } else {
            throw new \Exception('Unknown Entity Type Code for processing (' . $this->getEntityTypeCode() . ')');
        }

    }
}
