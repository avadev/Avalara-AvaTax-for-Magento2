<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Queue;

use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\Queue;

/**
 * Class Summary
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var Collection
     */
    protected $queueCollection;

    /**
     * @var array
     */
    protected $summaryData;

    /**
     * Summary constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory $queueCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $queueCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getQueueCollection()
    {
        // Initialize the queue collection
        if ($this->summaryData == null)
        {
            /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
            $this->queueCollection = $this->queueCollectionFactory->create();
        }
        return $this->queueCollection;
    }

    public function getQueueSummaryLastUpdatedAt()
    {
        $collection = $this->getQueueCollection();
        $lastUpdatedAt = $collection->getQueueSummaryLastProcessed();

        if ($lastUpdatedAt == null)
        {
            return '';
        }

        // Match the date time format in the columns for the queue records
        $dateTimeFormat = 'M d, Y h:i:s A';

        $localTime = date($dateTimeFormat, strtotime($lastUpdatedAt) + $this->dateTime->getGmtOffset());

        return $localTime;
    }

    public function getQeueuSummaryCount()
    {
        return $this->getQueueCollection()->getQueueSummaryCount(Queue::QUEUE_STATUS_PENDING);
    }
}
