<?php
namespace ClassyLlama\AvaTax\Observer;

use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class PreventOrderPlaceObserver
 */
class PreventOrderPlaceObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * QuoteManagement constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Config $config
     */
    public function __construct(\Magento\Framework\Registry $coreRegistry, Config $config)
    {
        $this->coreRegistry = $coreRegistry;
        $this->config = $config;
    }

    /**
     * If AvaTax GetTaxRequest failed and if configuration is set to prevent checkout, throw exception
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getOrder();
        if ($this->coreRegistry->registry(Tax::AVATAX_GET_TAX_REQUEST_ERROR)) {
            $errorMessage = $this->config->getErrorActionDisableCheckoutMessage($order->getStoreId());
            throw new LocalizedException($errorMessage);
        }
        return $this;
    }
}