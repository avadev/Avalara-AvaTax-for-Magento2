<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Queue;

use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\Queue;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Summary
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    // Match the date time format in the columns for the queue records
    const GRID_COLUMN_DATE_FORMAT = 'M d, Y h:i:s A';

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

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
        TimezoneInterface $localeDate,
        array $data = []
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->localeDate = $localeDate;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getQueueCollection()
    {
        // Initialize the queue collection
        if ($this->summaryData == null) {
            /** @var $queueCollection \ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection */
            $this->queueCollection = $this->queueCollectionFactory->create();
        }
        return $this->queueCollection;
    }

    /**
     * @return bool|string
     */
    public function getQueueSummaryLastUpdatedAt()
    {
        $collection = $this->getQueueCollection();
        $lastUpdatedAt = $collection->getQueueSummaryLastProcessed();

        if ($lastUpdatedAt == null) {
            return '';
        }

        $localTime = $this->getFormattedDate($lastUpdatedAt);

        return $localTime;
    }

    /**
     * @return int
     */
    public function getQueueSummaryCount()
    {
        return $this->getQueueCollection()->getQueueSummaryCount(Queue::QUEUE_STATUS_PENDING);
    }

    /**
     * Return date in the default timezone, formatted the same was as the dates in the grid columns
     *
     * @param null $time
     * @return string
     */
    protected function getFormattedDate($time = null)
    {
        $time = $time ?: 'now';
        $timezone = $this->localeDate->getConfigTimezone();
        $date = new \DateTime($time, new \DateTimeZone($this->localeDate->getDefaultTimezone()));
        $date->setTimezone(new \DateTimeZone($timezone));
        return $date->format(self::GRID_COLUMN_DATE_FORMAT);
    }
}
