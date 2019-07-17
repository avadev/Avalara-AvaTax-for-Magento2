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

namespace ClassyLlama\AvaTax\Model\Message;

use Magento\Framework\Notification\MessageInterface;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;

/**
 * Notifications class
 */
class QueueFailureNotification implements MessageInterface
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $queueFailureStats;

    /**
     * @var int
     */
    protected $queueFailureCount;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $avaTaxConfig
     * @param CollectionFactory $queueCollectionFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $avaTaxConfig,
        CollectionFactory $queueCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->storeManager = $storeManager;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        // Each week there are failures would introduce a new identity for the Message
        // and prompt the admin user to acknowledge the notification.
        return sha1('AVATAX_QUEUE_FAILURE_' . implode(':', array_keys($this->getQueueFailureStats())));
    }

    /**
     * Check whether notification is displayed
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // Check configuration to see if this should be evaluated further
        if (
            $this->avaTaxConfig->isModuleEnabled() == false ||
            $this->avaTaxConfig->getTaxMode($this->storeManager->getDefaultStoreView()) !=
                Config::TAX_MODE_ESTIMATE_AND_SUBMIT ||
            $this->avaTaxConfig->getQueueFailureNotificationEnabled() == false
        ) {
            return false;
        }

        // Query the database to get some stats about the queue
        $this->getQueueFailureStats();

        // Determine if we need to notify the admin user
        if (
            $this->authorization->isAllowed('ClassyLlama_AvaTax::manage_avatax') &&
            $this->queueFailureCount > 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load the queue stats
     */
    public function getQueueFailureStats()
    {
        // check to see if we've already loaded the queue stats
        if ($this->queueFailureStats === null) {
            $queueCollection = $this->queueCollectionFactory->create();

            // get the stats from the collection
            $this->queueFailureStats = $queueCollection->getQueueFailureStats();
            $this->queueFailureCount = array_sum($this->queueFailureStats);
        }
        return $this->queueFailureStats;
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        // Make sure we have the queue stats loaded
        $this->getQueueFailureStats();

        $messageDetails = __('The AvaTax Queue has %1 document(s) that failed during processing and were not ' .
            'successfully sent to AvaTax<br />' .
            '<a href="%2">Check the queue</a><br />',
            $this->queueFailureCount,
            $this->urlBuilder->getUrl('avatax/queue')
        );

        return $messageDetails;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_MAJOR;
    }
}
