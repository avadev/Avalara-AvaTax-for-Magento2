<?php

namespace ClassyLlama\AvaTax\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
    const ENTITY_TYPE_CODE_ORDER = 'order';

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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory
     */
    protected $orderStatusHistoryFactory;

    /**
     * @var \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusHistoryRepository;

    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param Get $interactionGetTax
     * @param TimezoneInterface $localeDate
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        //\Magento\Sales\Api\InvoiceCommentRepositoryInterface $invoiceCommentRepository, /* TODO: would prefer to use the repository to save but it was getting caught in an endless loop */
        //\Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory $invoiceCommentFactory, /* TODO: would prefer to use interface but no save method exists */
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        Get $interactionGetTax,
        TimezoneInterface $localeDate,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->invoiceManagement = $invoiceManagement;
        $this->invoiceRepository = $invoiceRepository;
        //$this->invoiceCommentRepository = $invoiceCommentRepository;
        $this->invoiceCommentFactory = $invoiceCommentFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->interactionGetTax = $interactionGetTax;
        $this->storeRepository = $storeRepository;
        $this->eavConfig = $eavConfig;
        $this->localeDate = $localeDate;
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

        // TODO: get entity
        // TODO: add comment to entity indicating what was done
        if ($this->getEntityTypeCode() === self::ENTITY_TYPE_CODE_INVOICE) {

            /* @var $invoice \Magento\Sales\Api\Data\InvoiceInterface */
            $invoice = $this->invoiceRepository->get($this->getEntityId());
            if ($invoice->getEntityId()) {

                // TODO: process entity with AvaTax
                try {
                    /* @var $processSalesResponse \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Response */
                    $processSalesResponse = $this->interactionGetTax->processSalesObject($invoice);

                    $message = "";
                    $message .= "Invoice #". $invoice->getIncrementId() . " was submitted to AvaTax";
                    if ($processSalesResponse->getIsUnbalanced()) {
                        // TODO: add comment about unbalanced amount
                        $invoice->getBaseTaxAmount();
                        $message .= "<br/>" .
                                    "When submitting the invoice to AvaTax the amount calculated for tax differed from what was recorded in Magento.<br/>" .
                                    "There was a difference of " . ($invoice->getBaseTaxAmount() - $processSalesResponse->getBaseAvataxTaxAmount()) . "<br/>" .
                                    "Magento listed a tax amount of " . $invoice->getBaseTaxAmount() . "<br/>" .
                                    "AvaTax calculated the tax to be " . $processSalesResponse->getBaseAvataxTaxAmount() . "<br/>";
                    }

                    // TODO: update invoice with additional fields
                    $processSalesResponse->getIsUnbalanced();
                    $processSalesResponse->getBaseAvataxTaxAmount();

                    //$allComments = $this->invoiceManagement->getCommentsList($invoice->getEntityId());

                    /* @var $order \Magento\Sales\Api\Data\OrderInterface */
                    $order = $this->orderRepository->get($invoice->getOrderId());
                    $store = $this->storeRepository->getById($invoice->getStoreId());
                    //$entityType = $this->eavConfig->getEntityType($order->getEntityType());

                    $message .= "<br/>" . $this->getFormattedDate($store);

                    // Add comment to order
                    $orderStatusHistory = $this->orderStatusHistoryFactory->create();
                    $orderStatusHistory->setParentId($invoice->getOrderId());
                    $orderStatusHistory->setComment($message);
                    $orderStatusHistory->setIsCustomerNotified(false);
                    $orderStatusHistory->setIsVisibleOnFront(false);
                    $orderStatusHistory->setEntityName(self::ENTITY_TYPE_CODE_ORDER);
                    $orderStatusHistory->setStatus($order->getStatus());


                    // $this->orderStatusHistoryRepository->save($orderStatusHistory);




                    $this->orderManagement->addComment($invoice->getOrderId(), $orderStatusHistory);

//                    $commentFromFactory = $this->invoiceCommentFactory->create();
//                    $commentFromFactory->setParentId($invoice->getEntityId());
//                    $commentFromFactory->setComment($message);
//                    $commentFromFactory->setIsCustomerNotified(false);
//                    $commentFromFactory->setIsVisibleOnFront(false);

                    // save the comment
                    //$this->invoiceCommentRepository->save($comment_from_repository);
//                    $commentFromFactory->save();
                } catch (Get\Exception $e) {

                }

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

    /**
     * Return date in the current scope's timezone, formatted in AvaTax format
     *
     * @param $scope
     * @param null $time
     * @return string
     */
    protected function getFormattedDate($scope, $time = null)
    {
        $time = $time ?: 'now';
        $timezone = $this->localeDate->getConfigTimezone(null, $scope);
        $date = new \DateTime($time, new \DateTimeZone($this->localeDate->getDefaultTimezone()));
        $date->setTimezone(new \DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    }
}
