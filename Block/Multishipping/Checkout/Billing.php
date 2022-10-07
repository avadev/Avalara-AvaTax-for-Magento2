<?php

declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\Multishipping\Checkout;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Block\Checkout\Billing as MultiShippingBilling;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Payment\Model\Method\SpecificationInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class Billing
 *
 * @package ClassyLlama\AvaTax\Block\Multishipping\Checkout
 */
class Billing extends MultiShippingBilling
{
    /**
     * @var AddressValidation
     */
    private $addressValidation;

    /**
     * Billing constructor.
     *
     * @param Context $context
     * @param Data $paymentHelper
     * @param SpecificationFactory $methodSpecificationFactory
     * @param Multishipping $multishipping
     * @param Session $checkoutSession
     * @param SpecificationInterface $paymentSpecification
     * @param AddressValidation $addressValidation
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        Context $context,
        Data $paymentHelper,
        SpecificationFactory $methodSpecificationFactory,
        Multishipping $multishipping,
        Session $checkoutSession,
        SpecificationInterface $paymentSpecification,
        AddressValidation $addressValidation,
        array $data = [],
        array $additionalChecks = []
    ) {
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $multishipping, $checkoutSession,
            $paymentSpecification, $data, $additionalChecks);
        $this->addressValidation = $addressValidation;
    }

    /**
     * @return mixed
     */
    public function isValidationEnabled()
    {
        return $this->addressValidation->isValidationEnabled();
    }

    /**
     * @param AddressInterface | Address $address
     * @return array
     * @throws AddressValidateException
     * @throws AvataxConnectionException
     * @throws LocalizedException
     */
    public function validateAddress($address)
    {
        return $this->addressValidation->validateAddress($address);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
