<?php

namespace ClassyLlama\AvaTax\Plugin\Quote\Model;

use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class QuoteManagement
 */
class QuoteManagement
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
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $orderData
     * @throws LocalizedException
     */
    public function beforeSubmit(
        \Magento\Quote\Model\QuoteManagement $subject,
        \Magento\Quote\Model\Quote $quote,
        $orderData = []
    ) {
        // TODO: Switch to using sales_order_place_before event
        if ($this->coreRegistry->registry(Tax::AVATAX_GET_TAX_REQUEST_ERROR)) {
            $errorMessage = $this->config->getErrorActionDisableCheckoutMessage($quote->getStoreId());
            throw new LocalizedException($errorMessage);
        }
    }
}