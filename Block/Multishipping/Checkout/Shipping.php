<?php

declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\Multishipping\Checkout;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\DataObject\GridFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Tax\Helper\Data;

/**
 * Class Shipping
 *
 * @package ClassyLlama\AvaTax\Block\Multishipping\Checkout
 */
class Shipping extends \Magento\Multishipping\Block\Checkout\Shipping
{


    /**
     * @var AddressValidation
     */
    private $addressValidation;

    /**
     * Shipping constructor.
     *
     * @param Context $context
     * @param GridFactory $filterGridFactory
     * @param Multishipping $multishipping
     * @param Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param AddressValidation $addressValidation
     * @param array $data
     */
    public function __construct(
        Context $context,
        GridFactory $filterGridFactory,
        Multishipping $multishipping,
        Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        AddressValidation $addressValidation,
        array $data = []
    ) {
        parent::__construct($context, $filterGridFactory, $multishipping, $taxHelper, $priceCurrency, $data);
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
     * @param AddressInterface $address
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
    public function getStoreCode(): string
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
