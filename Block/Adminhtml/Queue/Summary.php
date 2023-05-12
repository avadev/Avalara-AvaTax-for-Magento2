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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Queue;

use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\Queue;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Summary
 */
/**
 * @codeCoverageIgnore
 */
class Summary extends Template
{
    /**
     * Match the date time format in the columns for the queue records
     */
    const GRID_COLUMN_DATE_FORMAT = 'M d, Y h:i:s A';

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

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
     * @param Context $context
     * @param CollectionFactory $queueCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $queueCollectionFactory,
        array $data = []
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
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
        $timezone = $this->_localeDate->getConfigTimezone();
        $date = new \DateTime($time, new \DateTimeZone($this->_localeDate->getDefaultTimezone()));
        $date->setTimezone(new \DateTimeZone($timezone));
        return $date->format(self::GRID_COLUMN_DATE_FORMAT);
    }
}
