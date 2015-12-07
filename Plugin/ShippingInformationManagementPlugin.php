<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformation;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Customer\Model\Address\Mapper;

class ShippingInformationManagementPlugin
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    protected $shippingInformation = null;

    protected $quoteRepository = null;

    /**
     * @var PaymentDetailsExtensionFactory|null
     */
    protected $paymentDetailsExtensionFactory = null;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $customerAddressRepository;

    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param ValidationInteraction $validationInteraction
     * @param ShippingInformation $shippingInformation
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDetailsExtensionFactory $paymentDetailsExtensionFactory
     * @param AddressRepositoryInterface $customerAddressRepository
     * @param Copy $objectCopyService
     * @param Mapper $addressMapper
     * @param Config $config
     */
    public function __construct(
        ValidationInteraction $validationInteraction,
        ShippingInformation $shippingInformation,
        CartRepositoryInterface $quoteRepository,
        PaymentDetailsExtensionFactory $paymentDetailsExtensionFactory,
        AddressRepositoryInterface $customerAddressRepository,
        Copy $objectCopyService,
        Mapper $addressMapper,
        Config $config
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->shippingInformation = $shippingInformation;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDetailsExtensionFactory = $paymentDetailsExtensionFactory;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->objectCopyService = $objectCopyService;
        $this->addressMapper = $addressMapper;
        $this->config = $config;
    }

    public function aroundSaveAddressInformation(
        ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();

        $shippingInformationExtension = $addressInformation->getExtensionAttributes();

        $errorMessage = '';
        $validAddress = null;
        $customerAddress = null;

        $shouldValidateAddress = true;
        if (!is_null($shippingInformationExtension)) {
            $shouldValidateAddress = $shippingInformationExtension->getShouldValidateAddress();
        }

        $customerAddressId = $shippingAddress->getCustomerAddressId();

        $enabledAddressValidationCountries = explode(',', $this->config->getAddressValidationCountriesEnabled());
        if (!in_array($shippingAddress->getCountryId(), $enabledAddressValidationCountries)) {
            $shouldValidateAddress = false;
        }

        if ($shouldValidateAddress) {
            try {
                $validAddress = $this->validationInteraction->validateAddress($shippingAddress);
                if (!is_null($validAddress)) {
                    if ($customerAddressId) {
//                        $customerAddress = $this->customerAddressRepository->getById($customerAddressId);
//                        $this->mapQuoteAddressData($validAddress, $customerAddress);
//                        $validAddress = $validAddress->importCustomerAddressData($customerAddress);
//                        $this->customerAddressRepository->save($customerAddress);
                    } else {
                        $addressInformation->setShippingAddress($validAddress);
                    }
                }
            } catch (AddressValidateException $e) {
                $errorMessage = $e->getMessage();
            }
        }
//        else {
//            // Is this really necessary? This will happen anyway in the original method.
//            if ($customerAddressId) {
//                $customerAddress = $this->customerAddressRepository->getById($customerAddressId);
//                $customerAddress->setData($shippingAddress);
//                $this->customerAddressRepository->save($customerAddress);
//            }
//        }

        $returnValue = $proceed($cartId, $addressInformation);

        if (!$shouldValidateAddress) {
            return $returnValue;
        }

        $paymentDetailsExtension = $returnValue->getExtensionAttributes();

        if (is_null($paymentDetailsExtension)) {
            $paymentDetailsExtension = $this->paymentDetailsExtensionFactory->create();
        }

        if (!is_null($validAddress)) {
            $paymentDetailsExtension->setValidAddress($validAddress);
        } else {
            $paymentDetailsExtension->setErrorMessage($errorMessage);
        }

        $paymentDetailsExtension->setOriginalAddress($shippingAddress);

        $returnValue->setExtensionAttributes($paymentDetailsExtension);

        return $returnValue;
    }

    public function mapQuoteAddressData(\Magento\Quote\Model\Quote\Address $address, $customerAddress) {
        $sourceData = $address->getData();
        if (isset($sourceData['street']) && !is_array($sourceData['street'])) {
            $sourceData['street'] = [$sourceData['street']];
        }

        $this->objectCopyService->copyFieldsetToTarget(
            'avatax_quote_address',
            'to_customer_address',
            $sourceData,
            $customerAddress
        );
    }
}