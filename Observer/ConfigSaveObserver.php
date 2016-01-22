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

namespace ClassyLlama\AvaTax\Observer;

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ConfigSaveObserver
 */
class ConfigSaveObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \ClassyLlama\AvaTax\Helper\ModuleChecks
     */
    protected $moduleChecks;

    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax
     */
    protected $interactionTax;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config $config
     * @param \ClassyLlama\AvaTax\Helper\ModuleChecks $moduleChecks
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Tax $interactionTax
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $config,
        \ClassyLlama\AvaTax\Helper\ModuleChecks $moduleChecks,
        \ClassyLlama\AvaTax\Framework\Interaction\Tax $interactionTax
    ) {
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->moduleChecks = $moduleChecks;
        $this->interactionTax = $interactionTax;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $observer->getStore();

        foreach ($this->getErrors($store) as $error) {
            $this->messageManager->addError($error);
        }

        foreach ($this->getNotices() as $notice) {
            $this->messageManager->addNotice($notice);
        }

        return $this;
    }

    /**
     * Get all errors that should display when tax config is saved
     *
     * @param $store
     * @return array
     */
    protected function getErrors($store)
    {
        $errors = array();
        $errors = array_merge(
            $errors,
            $this->sendPing($store)
        );

        return $errors;
    }

    /**
     * Get all notices  that should display when tax config is saved
     *
     * @return array
     */
    protected function getNotices()
    {
        $notices = array();
        $notices = array_merge(
            $notices,
            // This check is also being displayed at the top of the page via
            // \ClassyLlama\AvaTax\Model\Message\ConfigNotification, but it's not as visible as a notice message, so
            // also add it as a notice.
            $this->moduleChecks->checkNativeTaxRules()
        );

        return $notices;
    }

    /**
     * Ping AvaTax using configured live/production mode
     *
     * @param $store
     * @return array
     */
    protected function sendPing($store)
    {
        $errors = [];
        if (!$this->config->isModuleEnabled($store)) {
            return $errors;
        }

        $message = '';
        $type = $this->config->getLiveMode() ? Config::API_PROFILE_NAME_PROD : Config::API_PROFILE_NAME_DEV;
        try {
            $result = $this->interactionTax->getTaxService($type)->ping();
            if (is_object($result) && $result->getResultCode() != \AvaTax\SeverityLevel::$Success) {
                foreach ($result->getMessages() as $messages) {
                    $message .= $messages->getName() . ': ' . $messages->getSummary() . "\n";
                }
            } elseif (is_object($result) && $result->getResultCode() == \AvaTax\SeverityLevel::$Success) {
                $this->messageManager->addSuccess(
                    __('Successfully connected to AvaTax using the '
                        . '<a href="#row_tax_avatax_connection_settings_header">%1 credentials</a>', $type
                    )
                );
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }

        if ($message) {
            $errors[] = __('Error connecting to AvaTax using the '
                . '<a href="#row_tax_avatax_connection_settings_header">%1 credentials</a>: %2', $type, $message);
        }
        return $errors;
    }
}
