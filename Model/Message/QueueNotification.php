<?php

namespace ClassyLlama\AvaTax\Model\Message;

use Magento\Framework\Notification\MessageInterface;

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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /*
     * Stores with invalid display settings
     *
     * @var array
     */
    protected $storesWithInvalidDisplaySettings;

    /*
     * Websites with invalid discount settings
     *
     * @var array
     */
    protected $storesWithInvalidDiscountSettings;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('AVATAX_QUEUE_NOTIFICATION');
    }

    /**
     * Check whether notification is displayed
     * TODO: Checks settings to make sure there are no conflicts
     * TODO: Checks queue to see if there are any items that have an excessive time pending/unsent
     * TODO: Checks connectivity test to AvaTax services (ping) on admin save?
     *
     * @return bool
     */
    public function isDisplayed()
    {
        //TODO: Logic for determining if notification should be displayed
        return false;
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        //TODO: Create actual error/warning message
        $messageDetails = 'Example error message for AvaTax module. ' . date(DATE_ATOM);

        return $messageDetails;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        //TODO: Determine if this should be critical or just a warning.
        // Critical seems to show the message all the time at the top of the page,
        // where I think warnings just show the flag.
        return MessageInterface::SEVERITY_CRITICAL;
    }
}
