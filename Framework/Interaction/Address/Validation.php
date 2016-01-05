<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use AvaTax\SeverityLevel;
use AvaTax\TextCase;
use AvaTax\ValidateRequestFactory;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Model\Session;
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
     * @var Session
     */
    protected $session = null;

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
        Session $session,
        ValidateRequestFactory $validateRequestFactory
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->session = $session;
        $this->validateRequestFactory = $validateRequestFactory;
    }

    /**
     * Using test AvaTax file contents to do a sample validate test
     * TODO: request or implement an interface for /AvaTax/Address and /AvaTax/ValidAddress since they can't extend because of SoapClient bug
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $addressInput
     * @return \Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|null
     * @throws AddressValidateException
     * @throws LocalizedException
     */
    public function validateAddress($addressInput)
    {
        $addressService = $this->interactionAddress->getAddressService();

        // TODO: Move try to be only around SOAP request calls.  Other exceptions should fall through.
        try {
            $returnCoordinates = 1;
            $avataxAddress = $this->interactionAddress->getAddress($addressInput);

            $validAddress = $this->session->getAddressResponse($avataxAddress);

            if (is_null($validAddress)) {
                $validateRequest = $this->validateRequestFactory->create(
                    [
                        'address' => $avataxAddress,
                        'textCase' => (TextCase::$Mixed ? TextCase::$Mixed : TextCase::$Default),
                        'coordinates' => $returnCoordinates,
                    ]
                );
                $validateResult = $addressService->Validate($validateRequest);
                $resultCode = $validateResult->getResultCode();
            } else {
                $resultCode = SeverityLevel::$Success;
            }

            if ($resultCode == SeverityLevel::$Success) {

                if (is_null($validAddress)) {
                    $validAddresses = $validateResult->getValidAddresses();
                    if (isset($validAddresses[0])) {
                        $validAddress = $validAddresses[0];
                        $this->session->addAddressResponse($avataxAddress, $validAddress);
                    } else {
                        return null;
                    }
                }
                // Convert data back to the type it was passed in as
                // TODO: Return null if address could not be converted to original type
                switch (true) {
                    case ($addressInput instanceof \Magento\Customer\Api\Data\AddressInterface):
                        $validAddress = $this->interactionAddress
                            ->convertAvaTaxValidAddressToCustomerAddress($validAddress, $addressInput);
                        break;
                    case ($addressInput instanceof \Magento\Quote\Api\Data\AddressInterface):
                        $validAddress = $this->interactionAddress
                            ->convertAvaTaxValidAddressToQuoteAddress($validAddress, $addressInput);
                        break;
                    case ($addressInput instanceof \Magento\Sales\Api\Data\OrderAddressInterface):
                        $validAddress = $this->interactionAddress
                            ->convertAvaTaxValidAddressToOrderAddress($validAddress, $addressInput);
                        break;
                    case (is_array($addressInput)):
                        $validAddress = $this->interactionAddress->convertAvaTaxValidAddressToArray($validAddress);
                        break;
                }

                return $validAddress;
            } else {
                throw new AddressValidateException(new Phrase($validateResult->getMessages()[0]->getSummary()));
            }
        } catch (\SoapFault $exception) {
            throw new LocalizedException(new Phrase($exception->getMessage()));
        }
    }
}