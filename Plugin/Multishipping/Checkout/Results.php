<?php

namespace ClassyLlama\AvaTax\Plugin\Multishipping\Checkout;

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
     * Results constructor.
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Session\Generic $session
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Session\Generic $session
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
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
}
