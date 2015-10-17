<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use ClassyLlama\AvaTax\Framework\Interaction\Address\AddressAbstract;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\DataObject;
use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\ValidateRequestFactory;
use AvaTax\TextCase;
use AvaTax\SeverityLevel;

class Validation extends AddressAbstract
{

    /**
     * @var ValidateRequestFactory
     */
    protected $validateRequestFactory = null;

    /**
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     * @param ValidateRequestFactory $validateRequestFactory
     */
    public function __construct(
        ATConfigFactory $avaTaxConfigFactory,
        Config $config,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory,
        ValidateRequestFactory $validateRequestFactory
    ) {
        $this->validateRequestFactory = $validateRequestFactory;
        parent::__construct(
            $avaTaxConfigFactory,
            $config,
            $addressFactory,
            $addressServiceSoapFactory
        );
    }

    /**
     * Using test AvaTax file contents to do a sample validate test
     * TODO: Abstract out necessary components and anonymize usage
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return string
     */
    public function validateAddress()
    {
        $response = '';
        $addressSvc = $this->addressServiceSoapFactory->create(['configurationName' => 'Development']);
        try
        {
            $address = $this->addressFactory->create();
            $address->setLine1("4064 S. Lone Pine Ave.");
            $address->setLine2("");
            $address->setLine3("");
            $address->setCity("Springfield");
            $address->setRegion("MO");
            $address->setPostalCode("65804");
            $textCase = TextCase::$Mixed;
            $coordinates = 1;
//Request
            $validateRequest = $this->validateRequestFactory->create(
                [
                    'address' => $address,
                    'textCase' => ($textCase ? $textCase : TextCase::$Default),
                    'coordinates' => $coordinates,
                ]
            );
            $validateResult = $addressSvc->Validate($validateRequest);
//Results
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
                foreach ($validateResult->getvalidAddresses() as $valid)
                {
                    $response .= "Line 1: " . $valid->getline1() . "\n";
                    $response .= "Line 2: " . $valid->getline2() . "\n";
                    $response .= "Line 3: " . $valid->getline3() . "\n";
                    $response .= "Line 4: " . $valid->getline4() . "\n";
                    $response .= "City: " . $valid->getcity() . "\n";
                    $response .= "Region: " . $valid->getregion() . "\n";
                    $response .= "Postal Code: " . $valid->getpostalCode() . "\n";
                    $response .= "Country: " . $valid->getcountry() . "\n";
                    $response .= "County: " . $valid->getcounty() . "\n";
                    $response .= "FIPS Code: " . $valid->getfipsCode() . "\n";
                    $response .= "PostNet: " . $valid->getpostNet() . "\n";
                    $response .= "Carrier Route: " . $valid->getcarrierRoute() . "\n";
                    $response .= "Address Type: " . $valid->getaddressType() . "\n";
                    if ($coordinates == 1)
                    {
                        $response .= "Latitude: " . $valid->getlatitude() . "\n";
                        $response .= "Longitude: " . $valid->getlongitude() . "\n";
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
            $response .= $addressSvc->__getLastRequest() . "\n";
            $response .= $addressSvc->__getLastResponse() . "\n   ";
        }

        return $response;
    }
}