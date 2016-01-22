<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use AvaTax\SeverityLevel;
use AvaTax\TextCase;
use AvaTax\ValidateRequestFactory;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\Cacheable\AddressService;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Validation
{
    /**
     * @var Address
     */
    protected $interactionAddress = null;

    /**
     * @var AddressService
     */
    protected $addressService = null;

    /**
     * @var ValidateRequestFactory
     */
    protected $validateRequestFactory = null;

    /**
     * Error message to use when response does not contain error messages
     */
    const GENERIC_VALIDATION_MESSAGE = 'An unknown address validation error occurred';

    /**
     * @param Address $interactionAddress
     * @param AddressService $addressService
     * @param ValidateRequestFactory $validateRequestFactory
     */
    public function __construct(
        Address $interactionAddress,
        AddressService $addressService,
        ValidateRequestFactory $validateRequestFactory
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->addressService = $addressService;
        $this->validateRequestFactory = $validateRequestFactory;
    }

    /**
     * Validate address using AvaTax Address Validation API
     *
     * @param array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @return array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @throws AddressValidateException
     * @throws LocalizedException
     */
    public function validateAddress($addressInput)
    {
        $returnCoordinates = 1;
        $validateRequest = $this->validateRequestFactory->create(
            [
            'address' => $this->interactionAddress->getAddress($addressInput),
                'textCase' => (TextCase::$Mixed ? TextCase::$Mixed : TextCase::$Default),
                'coordinates' => $returnCoordinates,
            ]
        );
        $validateResult = $this->addressService->validate($validateRequest);

        if ($validateResult->getResultCode() == SeverityLevel::$Success) {
            $validAddresses = $validateResult->getValidAddresses();

            if (isset($validAddresses[0])) {
                $validAddress = $validAddresses[0];
            } else {
                return null;
            }
            // Convert data back to the type it was passed in as
            switch (true) {
                case ($addressInput instanceof \Magento\Customer\Api\Data\AddressInterface):
                    $validAddress = $this->interactionAddress
                        ->convertAvaTaxValidAddressToCustomerAddress($validAddress, $addressInput);
                    break;
                case ($addressInput instanceof \Magento\Quote\Api\Data\AddressInterface):
                    $validAddress = $this->interactionAddress
                        ->convertAvaTaxValidAddressToQuoteAddress($validAddress, $addressInput);
                    break;
                default:
                    throw new LocalizedException(__(
                        'Input parameter "$addressInput" was not of a recognized/valid type: "%1".',
                        [gettype($addressInput),]
                    ));
                    break;
            }

            return $validAddress;
        } else {
            $firstMessage = array_shift($validateResult->getMessages());
            $message = $firstMessage instanceof \AvaTax\Message
                ? $firstMessage->getSummary()
                : self::GENERIC_VALIDATION_MESSAGE;
            throw new AddressValidateException(__($message));
        }
    }
}
