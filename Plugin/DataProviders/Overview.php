<?php

namespace ClassyLlama\AvaTax\Plugin\DataProviders;

/**
 * Class Overview
 *
 * @package ClassyLlama\AvaTax\Plugin\DataProviders
 */
class Overview
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * Overview constructor.
     *
     * @param \Magento\Framework\Session\Generic $session
     */
    public function __construct(
        \Magento\Framework\Session\Generic $session
    ) {
        $this->session = $session;
    }

    /**
     * Returns all stored address errors.
     *
     * @return array
     */
    public function afterGetAddressErrors(\Magento\Multishipping\Block\DataProviders\Overview $subject, $result): array
    {
        $addresses = [];
        if (!empty($this->session->getAvataxGetTaxRequestErrorIds())) {
            $ids = $this->session->getAvataxGetTaxRequestErrorIds();
            foreach ($result as $id => $address) {
                if (in_array($id, $ids)) {
                    $addresses[$id] = $address;
                }
            }
        }

        return $addresses;
    }
}
