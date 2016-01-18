<?php

namespace ClassyLlama\AvaTax\Plugin\Checkout\Model;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use ClassyLlama\AvaTax\Framework\Interaction\Address as AddressInteraction;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformation;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\LocalizedException;

class ShippingInformationManagementPlugin
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    /**
     * @var AddressInteraction|null
     */
    protected $addressInteraction = null;

    /**
     * @var ShippingInformation|null
     */
    protected $shippingInformation = null;

    /**
     * @var CartRepositoryInterface|null
     */
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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * ShippingInformationManagementPlugin constructor
     *
     * @param ValidationInteraction $validationInteraction
     * @param AddressInteraction $addressInteraction
     * @param ShippingInformation $shippingInformation
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDetailsExtensionFactory $paymentDetailsExtensionFactory
     * @param AddressRepositoryInterface $customerAddressRepository
     * @param Copy $objectCopyService
     * @param Mapper $addressMapper
     * @param Config $config
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        ValidationInteraction $validationInteraction,
        AddressInteraction $addressInteraction,
        ShippingInformation $shippingInformation,
        CartRepositoryInterface $quoteRepository,
        PaymentDetailsExtensionFactory $paymentDetailsExtensionFactory,
        AddressRepositoryInterface $customerAddressRepository,
        Copy $objectCopyService,
        Mapper $addressMapper,
        Config $config,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->addressInteraction = $addressInteraction;
        $this->shippingInformation = $shippingInformation;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDetailsExtensionFactory = $paymentDetailsExtensionFactory;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->objectCopyService = $objectCopyService;
        $this->addressMapper = $addressMapper;
        $this->config = $config;
        $this->coreRegistry = $coreRegistry;
    }

    public function aroundSaveAddressInformation(
        ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        // Only validate address if module is enabled
        $quote = $this->quoteRepository->getActive($cartId);
        $storeId = $quote->getStoreId();
        if (!$this->config->isModuleEnabled($storeId)) {
            $paymentDetails = $proceed($cartId, $addressInformation);
            $this->ensureTaxCalculationSuccess($storeId);
            return $paymentDetails;
        }

        // Only validate address if address validation is enabled
        if (!$this->config->isAddressValidationEnabled($storeId)) {
            $paymentDetails = $proceed($cartId, $addressInformation);
            $this->ensureTaxCalculationSuccess($storeId);
            return $paymentDetails;
        }

        $shippingAddress = $addressInformation->getShippingAddress();

        $shippingInformationExtension = $addressInformation->getExtensionAttributes();

        $errorMessage = null;
        $validAddress = null;
        $customerAddress = null;
        $quoteAddress = null;

        $shouldValidateAddress = true;
        if (!is_null($shippingInformationExtension)) {
            $shouldValidateAddress = $shippingInformationExtension->getShouldValidateAddress();
        }

        $customerAddressId = $shippingAddress->getCustomerAddressId();

        $enabledAddressValidationCountries = explode(
            ',',
            $this->config->getAddressValidationCountriesEnabled($storeId)
        );
        if (!in_array($shippingAddress->getCountryId(), $enabledAddressValidationCountries)) {
            $shouldValidateAddress = false;
        }

        if ($shouldValidateAddress) {
            try {
                $validAddress = $this->validationInteraction->validateAddress($shippingAddress);
            } catch (AddressValidateException $e) {
                $errorMessage = $e->getMessage();
            } catch (\SoapFault $e) {
                // If there is a SoapFault, it will have already been logged, so just disable address validation, as we
                // don't want to display SoapFault error message to user
                $shouldValidateAddress = false;
            }
        }

        // Determine which address to save to the customer or shipping addresses
        if (!is_null($validAddress)) {
            $quoteAddress = $validAddress;
        } else {
            $quoteAddress = $shippingAddress;
        }

        if ($customerAddressId) {
            // Update the customer address
            $customerAddress = $this->customerAddressRepository->getById($customerAddressId);
            $mergedCustomerAddress = $this->addressInteraction->copyQuoteAddressToCustomerAddress(
                $quoteAddress,
                $customerAddress
            );
            $this->customerAddressRepository->save($mergedCustomerAddress);
        } else {
            // Update the shipping address
            $addressInformation->setShippingAddress($quoteAddress);
        }

        $returnValue = $proceed($cartId, $addressInformation);

        $this->ensureTaxCalculationSuccess($storeId);

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

    /**
     * Check to see if there was an error during tax calculation, and if so, throw exception to prevent further progress
     *
     * @param $storeId
     * @return void
     * @throws LocalizedException
     */
    protected function ensureTaxCalculationSuccess($storeId)
    {
        if ($this->coreRegistry->registry(Tax::AVATAX_GET_TAX_REQUEST_ERROR)) {
            $errorMessage = $this->config->getErrorActionDisableCheckoutMessage($storeId);
            throw new LocalizedException($errorMessage);
        }
    }
}
