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

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\Notification\MessageInterface;

/**
 * QueueDisabledNotification class
 */
class QueueDisabledNotification implements MessageInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return sha1('AVATAX_QUEUE_DISABLED_NOTIFICATION');
    }

    /**
     * Check whether notification is displayed
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->getText()
            && $this->isQueuePage()
            && $this->config->isModuleEnabled()
            && $this->config->getTaxMode($this->storeManager->getDefaultStoreView())
                != Config::TAX_MODE_ESTIMATE_AND_SUBMIT;
    }

    /**
     * Return whether page is queue page
     *
     * @return bool
     */
    protected function isQueuePage()
    {
        return $this->request->getModuleName() == 'avatax'
            && $this->request->getControllerName() == 'queue'
            && $this->request->getActionName() == 'index';
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        return __(
            'Queuing functionality is disabled as <strong>Tax Mode</strong> is <em>not</em> set to '
                . '<strong>Estimate Tax & Submit Transactions to AvaTax</strong> on the <a href="%1">Tax Configuration page</a>.',
            $this->urlBuilder->getUrl('admin/system_config/edit', ['section' => 'tax'])
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        // Critical messages will always show, which is what we want
        return MessageInterface::SEVERITY_CRITICAL;
    }
}
