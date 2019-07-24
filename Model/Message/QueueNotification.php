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
class QueueNotification implements MessageInterface
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
     * @var int
     */
    protected $statQueueCount;

    /**
     * @var string
     */
    protected $statQueueLastCreatedAt;

    /**
     * @var string
     */
    protected $statQueueLastUpdatedAt;

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
        return sha1('AVATAX_QUEUE_NOTIFICATION');
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
            $this->avaTaxConfig->getQueueAdminNotificationEnabled() == false
        ) {
            return false;
        }

        // Query the database to get some stats about the queue
        $this->loadQueueStats();

        // Determine if we need to notify the admin user
        if (
            $this->authorization->isAllowed('ClassyLlama_AvaTax::manage_avatax') &&
            $this->statQueueCount > 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load the queue stats
     */
    public function loadQueueStats()
    {
        // check to see if we've already loaded the queue stats
        if ($this->statQueueCount === null) {
            $queueCollection = $this->queueCollectionFactory->create();

            // get the stats from the collection
            $queueStats = $queueCollection->getQueuePendingMoreThanADay();

            // load the object properties with the stats
            $this->statQueueCount = $queueStats[$queueCollection::SUMMARY_COUNT_FIELD_NAME];
            $this->statQueueLastCreatedAt = $queueStats[$queueCollection::SUMMARY_LAST_CREATED_AT_FIELD_NAME];
            $this->statQueueLastUpdatedAt = $queueStats[$queueCollection::SUMMARY_LAST_UPDATED_AT_FIELD_NAME];
        }
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
        $this->loadQueueStats();

        $lastProcessedAt = $this->statQueueLastUpdatedAt > $this->statQueueLastCreatedAt ?
            $this->statQueueLastUpdatedAt : $this->statQueueLastCreatedAt;

        $messageDetails = __('The AvaTax Queue has not been processed in the last 24 hours and may indicate a system ' .
            'configuration issue for submitting order information to AvaTax for reporting purposes.<br />' .
            '<a href="%1">Check the queue</a><br />' .
            '<a href="%2">Check configuration settings</a><br />' .
            'There are %3 document(s) needing to be submitted to AvaTax and the last time ' .
            'any processing was attempted was at %4',
            $this->urlBuilder->getUrl('avatax/queue'),
            $this->urlBuilder->getUrl('admin/system_config/edit', ['section' => 'tax']),
            $this->statQueueCount,
            $lastProcessedAt
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
