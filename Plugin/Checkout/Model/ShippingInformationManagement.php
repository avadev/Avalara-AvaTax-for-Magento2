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

namespace ClassyLlama\AvaTax\Plugin\Checkout\Model;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use ClassyLlama\AvaTax\Framework\Interaction\Address as AddressInteraction;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformation;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ShippingInformationManagement
 */
class ShippingInformationManagement
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
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * ShippingInformationManagement constructor
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
     * @param \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
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
        \Magento\Framework\Registry $coreRegistry,
        \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
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
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
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

        // If quote is virtual, getShippingAddress will return billing address, so no need to check if quote is virtual
        $shippingAddress = $addressInformation->getShippingAddress();

        $shippingInformationExtension = $addressInformation->getExtensionAttributes();

        $errorMessage = null;
        $validAddress = null;
        $customerAddress = null;
        $quoteAddress = null;

        $shouldValidateAddress = true;
        if (
            !is_null($shippingInformationExtension)
            && $shippingInformationExtension->getShouldValidateAddress() !== null
        ) {
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
                $validAddress = $this->validationInteraction->validateAddress($shippingAddress, $storeId);
            } catch (AddressValidateException $e) {
                $errorMessage = $e->getMessage();
            } catch (AvataxConnectionException $e) {
                // If there is a connection error, it will have already been logged, so just disable address validation, as we
                // don't want to display error message to user
                $shouldValidateAddress = false;
            } catch (\Exception $e) {
                $this->avaTaxLogger->error(
                    'Error in validating address in aroundSaveAddressInformation: ' . $e->getMessage()
                );
                // Continue without address validation
                $shouldValidateAddress = false;
            }
        }

        // Determine which address to save to the customer or shipping addresses
        if (!is_null($validAddress)) {
            $quoteAddress = $validAddress;
        } else {
            $quoteAddress = $shippingAddress;
        }

        try {
            /*
             * Regardless of whether address was validated by AvaTax, if the address is a customer address then we need
             * to save that address on the customer record. The reason for this is that when a user is on the "Review
             * & Payments" step and they are selecting between "Valid" and "Original" address options, the selected
             * address information is submitted to this API so that the customer address is updated and tax
             * calculation is affected accordingly.
             */
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
        } catch (\Exception $e) {
            // There may be scenarios in which the above address updating may fail, in which case we should just do
            // nothing
            $this->avaTaxLogger->error('Error in saving address: ' . $e->getMessage());
            // Continue without address validation
            $shouldValidateAddress = false;
        }

        try {
            $returnValue = $proceed($cartId, $addressInformation);
        } catch (NoSuchEntityException $e) {
            if (
            (
                strpos(strtolower($e->getMessage()), 'carrier') !== false
                || strpos(strtolower($e->getMessage()), 'shipping') !== false
            )
                && strpos(strtolower($e->getMessage()), 'not found') !== false
            ) {
                // The exception that was thrown looks like it is one indicating the selected shipping method is no
                // longer valid; override this message with one that is more verbose
                throw new NoSuchEntityException(
                    __(
                        'Our address validation service has suggested that the provided address be updated to:<p>' .
                        $validAddress->getStreetFull() . '<br />' . $validAddress->getCity() . ', ' .
                        $validAddress->getRegion() . ' ' . $validAddress->getPostcode() .
                        '</p>However, the selected shipping method is not available for the updated address. Please ' .
                        'either update your address to match the address above or select a different shipping method ' .
                        'to continue.'
                    )
                );
            }
            // The exception that was thrown is not the specific one we were looking for, bubble up the original error
            throw new NoSuchEntityException(__($e->getMessage()));
        }

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
