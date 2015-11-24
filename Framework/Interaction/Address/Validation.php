<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use AvaTax\SeverityLevel;
use AvaTax\TextCase;
use AvaTax\ValidateRequestFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Validation
{

    /**
     * @var ValidateRequestFactory
     */
    protected $validateRequestFactory = null;

    /**
     * @var Address
     */
    protected $interactionAddress = null;

    /**
     * @param Address $interactionAddress
     * @param ValidateRequestFactory $validateRequestFactory
     */
    public function __construct(
        Address $interactionAddress,
        ValidateRequestFactory $validateRequestFactory
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->validateRequestFactory = $validateRequestFactory;
    }

    /**
     * Using test AvaTax file contents to do a sample validate test
     * TODO: request or implement an interface for /AvaTax/Address and /AvaTax/ValidAddress since they can't extend because of SoapClient bug
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return /AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @throws LocalizedException
     */
    public function validateAddress($addressInput)
    {
        $addressService = $this->interactionAddress->getAddressService();

        // TODO: Move try to be only around SOAP request calls.  Other exceptions should fall through.
        try {
            $returnCoordinates = 1;
            $validateRequest = $this->validateRequestFactory->create(
                [
                    'address' => $this->interactionAddress->getAddress($addressInput),
                    'textCase' => (TextCase::$Mixed ? TextCase::$Mixed : TextCase::$Default),
                    'coordinates' => $returnCoordinates,
                ]
            );
            $validateResult = $addressService->Validate($validateRequest);

            if ($validateResult->getResultCode() == SeverityLevel::$Success) {
                $validAddresses = $validateResult->getValidAddresses();

                if (isset($validAddresses[0])) {
                    $validAddress =  $validAddresses[0];
                } else {
                    return null;
                }
                // Convert data back to the type it was passed in as
                switch (true) {
                    case ($addressInput instanceof \Magento\Customer\Api\Data\AddressInterface):
                        $validAddress = $this->interactionAddress->convertAvaTaxValidAddressToCustomerAddress($validAddress);
                        break;
                    case ($addressInput instanceof \Magento\Quote\Api\Data\AddressInterface):
                        $validAddress = $this->interactionAddress->convertAvaTaxValidAddressToOrderAddress($validAddress);
                        break;
                    case ($addressInput instanceof \Magento\Sales\Api\Data\OrderAddressInterface):
                        $validAddress = $this->interactionAddress->convertAvaTaxValidAddressToOrderAddress($validAddress);
                        break;
                    case (is_array($addressInput)):
                        $validAddress = $this->interactionAddress->convertAvaTaxValidAddressToArray($validAddress);
                        break;
                }

                return $validAddress;
            } else {
                return null;
            }
        } catch (\SoapFault $exception) {
            throw new LocalizedException(new Phrase($exception->getMessage()));
        }
    }
}