<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ClassyLlama\AvaTax\Model\AvaTax;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\ShippingInformation;
use ClassyLlama\AvaTax\Api\ValidAddressManagementInterface;
use ClassyLlama\AvaTax\Api\Data\ValidAddressInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidAddressManagement implements ValidAddressManagementInterface
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    protected $shippingInformation = null;

    protected $quoteRepository = null;

    public function __construct(
        ValidationInteraction $validationInteraction,
        ShippingInformation $shippingInformation,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->shippingInformation = $shippingInformation;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \ClassyLlama\AvaTax\Api\Data\ValidAddressInterface $validAddress
     * @return string[]
     */
    public function saveValidAddress(ValidAddressInterface $validAddress) {
        $validAddress = $validAddress->getValidAddress();
//        $validAddress = $this->validationInteraction->validateAddress($validAddress);
        return $validAddress;
    }
}
