<?php
namespace ClassyLlama\AvaTax\Observer;

use ClassyLlama\AvaTax\Model\Config;
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
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax
     */
    protected $interactionTax;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config $config
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Tax $interactionTax
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $config,
        \ClassyLlama\AvaTax\Framework\Interaction\Tax $interactionTax
    ) {
        $this->messageManager = $messageManager;
        $this->config = $config;
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
        $type = $this->config->getLiveMode($store) ? Config::API_PROFILE_NAME_PROD : Config::API_PROFILE_NAME_DEV;
        try {
            $result = $this->interactionTax->getTaxService($type)->ping();
            if (is_object($result) && $result->getResultCode() != \AvaTax\SeverityLevel::$Success) {
                foreach ($result->getMessages() as $messages) {
                    $message .= $messages->getName() . ': ' . $messages->getSummary() . "\n";
                }
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
