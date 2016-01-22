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

use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;
use ClassyLlama\AvaTax\Helper\Config;
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
