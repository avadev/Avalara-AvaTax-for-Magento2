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
 * ConfigNotification class
 */
class ConfigNotification implements MessageInterface
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \ClassyLlama\AvaTax\Helper\ModuleChecks
     */
    protected $moduleChecks;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Config $config
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \ClassyLlama\AvaTax\Helper\ModuleChecks $moduleChecks
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        Config $config,
        \Magento\Tax\Model\Config $taxConfig,
        \ClassyLlama\AvaTax\Helper\ModuleChecks $moduleChecks
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->config = $config;
        $this->taxConfig = $taxConfig;
        $this->moduleChecks = $moduleChecks;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return sha1('AVATAX_CONFIG_NOTIFICATION');
    }

    /**
     * Check whether notification is displayed
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->getText()
            && $this->isTaxConfigPage()
            && $this->config->isModuleEnabled();
    }

    /**
     * Return whether page is tax configuration
     *
     * @return bool
     */
    protected function isTaxConfigPage()
    {
        return $this->request->getModuleName() == 'admin'
            && $this->request->getControllerName() == 'system_config'
            && $this->request->getActionName() == 'edit'
            && $this->request->getParam('section') == 'tax';
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        return implode('<br><br>', $this->moduleChecks->getModuleCheckErrors());
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
