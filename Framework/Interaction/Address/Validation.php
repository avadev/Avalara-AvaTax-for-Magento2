<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\SeverityLevel;
use AvaTax\TextCase;
use AvaTax\ValidateRequestFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\DataObject;

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
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
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
     * @return string
     */
    public function validateAddress($addressInput)
    {

        $response = '';
        $addressService = $this->interactionAddress->getAddressService();
        try
        {
            $returnCoordinates = 1;
            $validateRequest = $this->validateRequestFactory->create(
                [
                    'address' => $this->interactionAddress->getAddress($addressInput),
                    'textCase' => (TextCase::$Mixed ? TextCase::$Mixed : TextCase::$Default),
                    'coordinates' => $returnCoordinates,
                ]
            );
            $validateResult = $addressService->Validate($validateRequest);

            $response .= "\n" . 'Validate ResultCode is: ' . $validateResult->getResultCode() . "\n";
            if ($validateResult->getResultCode() != SeverityLevel::$Success)
            {
                foreach ($validateResult->getMessages() as $message)
                {
                    $response .= $message->getName() . ": " . $message->getSummary() . "\n";
                }
            } else
            {
                $response .= "Normalized Address: \n";
                foreach ($validateResult->getValidAddresses() as $valid)
                {
                    /* @var $valid \AvaTax\ValidAddress */
                    $response .= "Line 1: " . $valid->getLine1() . "\n";
                    $response .= "Line 2: " . $valid->getLine2() . "\n";
                    $response .= "Line 3: " . $valid->getLine3() . "\n";
                    $response .= "Line 4: " . $valid->getLine4() . "\n";
                    $response .= "City: " . $valid->getCity() . "\n";
                    $response .= "Region: " . $valid->getRegion() . "\n";
                    $response .= "Postal Code: " . $valid->getPostalCode() . "\n";
                    $response .= "Country: " . $valid->getCountry() . "\n";
                    $response .= "County: " . $valid->getCounty() . "\n";
                    $response .= "FIPS Code: " . $valid->getFipsCode() . "\n";
                    $response .= "PostNet: " . $valid->getPostNet() . "\n";
                    $response .= "Carrier Route: " . $valid->getCarrierRoute() . "\n";
                    $response .= "Address Type: " . $valid->getAddressType() . "\n";
                    if ($returnCoordinates == 1)
                    {
                        $response .= "Latitude: " . $valid->getLatitude() . "\n";
                        $response .= "Longitude: " . $valid->getLongitude() . "\n";
                    }
                }
            }
        } catch (SoapFault $exception)
        {
            $message = "Exception: ";
            if ($exception)
            {
                $message .= $exception->faultstring;
            }
            $response .= $message . "\n";
            $response .= $addressService->__getLastRequest() . "\n";
            $response .= $addressService->__getLastResponse() . "\n   ";
        }

        return $response;
    }
}