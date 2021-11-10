<?php

namespace ClassyLlama\AvaTax\Plugin\Multishipping\Checkout;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Results
 *
 * @package ClassyLlama\AvaTax\Plugin\Multishipping\Checkout
 */
class Results
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Results constructor.
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Session\Generic $session
     * @param \ClassyLlama\AvaTax\Helper\Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Session\Generic $session,
        \ClassyLlama\AvaTax\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns all failed addresses from quote.
     *
     * @return array
     * @since 100.2.1
     */
    public function afterGetFailedAddresses(\Magento\Multishipping\Block\Checkout\Results $subject, $result): array
    {
        $addresses = [];
        if (!empty($this->session->getAvataxGetTaxRequestErrorIds())) {
            $ids = $this->session->getAvataxGetTaxRequestErrorIds();
            foreach ($result as $address) {
                if (($address->getAddressType() == \Magento\Sales\Model\Order\Address::TYPE_BILLING) || in_array($address->getId(), $ids)) {
                    $addresses[] = $address;
                }
            }
        }

        return $addresses;
    }

    /**
     * Returns address error.
     *
     * @param QuoteAddress $address
     * @return string
     * @since 100.2.1
     */
    public function afterGetAddressError(\Magento\Multishipping\Block\Checkout\Results $subject, $result, QuoteAddress $address): string
    {
        if (empty($result) && $address->getAddressType() === QuoteAddress::ADDRESS_TYPE_SHIPPING) {
            return $this->config->getErrorActionDisableCheckoutMessageFrontend($this->storeManager->getStore());
        }

        return $result;
    }
}
