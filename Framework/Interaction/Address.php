<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\AddressServiceSoap;
use ClassyLlama\AvaTax\Helper\Validation;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;


class Address
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var Validation
     */
    protected $validation = null;

    /**
     * @var AddressFactory
     */
    protected $addressFactory = null;

    /**
     * @var AddressServiceSoapFactory
     */
    protected $addressServiceSoapFactory = null;

    /**
     * @var AddressServiceSoap[]
     */
    protected $addressServiceSoap = [];

    /**
     * TODO: Add additional validation to make it exactly match the API documentation
     *
     * @var array
     */
    protected $validAddressFields = [
        'line1' => ['type' => 'string'],
        'line2' => ['type' => 'string'],
        'line3' => ['type' => 'string'],
        'city' => ['type' => 'string'],
        'region' => ['type' => 'string'],
        'postalCode' => ['type' => 'string'],
        'country' => ['type' => 'string'],
        'taxRegionId' => ['type' => 'integer'],
        'latitude' => ['type' => 'string'],
        'longitude' => ['type' => 'string'],
    ];

    /**
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     */
    public function __construct(
        Config $config,
        Validation $validation,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory
    ) {
        $this->config = $config;
        $this->validation = $validation;
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
    }

    /**
     * Get address service by type and cache instances by type to avoid duplicate instantiation
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param string $type
     * @return AddressServiceSoap
     */
    public function getAddressService($type = null)
    {
        if (is_null($type)) {
            $type = $this->config->getLiveMode() ? Config::API_PROFILE_NAME_PROD : Config::API_PROFILE_NAME_DEV;
        }
        if (!isset($this->addressServiceSoap[$type])) {
            $this->addressServiceSoap[$type] =
                $this->addressServiceSoapFactory->create(['configurationName' => $type]);
        }
        return $this->addressServiceSoap[$type];
    }

    /**
     * Get an AvaTax address object with fields as specified in data
     * TODO: See if I can take > 3 lines of address into account because at least in M1, you had the ability to choose up to 4 lines of addressCustomer Configuration -> Name and Address Options -> Number of Lines in a Street Address
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data array|AddressModelInterface|AddressInterface
     * @return \AvaTax\Address
     * @throws LocalizedException
     */
    public function getAddress($data)
    {
        switch (true) {
            case ($data instanceof AddressModelInterface):
                $data = $this->convertAddressModelToAvaTaxAddress($data);
                break;
            case ($data instanceof AddressInterface):
                $data = $this->convertServiceAddressToAvaTaxAddress($data);
                break;
            case (!is_array($data)):
                throw new LocalizedException(new Phrase(
                    'Input paramater "$data" was not of a recognized/valid type: "%1".', [
                        gettype($data),
                ]));
        }
        $data = $this->validation->validateData($data, $this->validAddressFields);
        return  $this->addressFactory->create($data);
    }

    /**
     * Converts Service Layer address into AvaTax compatible data array
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param AddressInterface $address
     * @return array
     */
    protected function convertServiceAddressToAvaTaxAddress(AddressInterface $address)
    {
        $street = $address->getStreet();

        return $data = [
            'line1' => array_key_exists(0, $street) ? $street[0] : '',
            'line2' => array_key_exists(1, $street) ? $street[1] : '',
            'line3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $address->getRegion()->getRegionCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }

    /**
     * Converts Service Layer address into AvaTax compatible data array
     *
     * 3 address types implement this interface with two of them extending AddressAbstract
     * All three have these methods but since there is no comprehensive interface to rely on
     * this could break in the future.
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param AddressInterface $address
     * @return array
     */
    protected function convertAddressModelToAvaTaxAddress(AddressModelInterface $address)
    {
        return $data = [
            'line1' => $address->getStreetLine(1),
            'line2' => $address->getStreetLine(2),
            'line3' => $address->getStreetLine(3),
            'city' => $address->getCity(),
            'region' => $address->getRegionCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }
}