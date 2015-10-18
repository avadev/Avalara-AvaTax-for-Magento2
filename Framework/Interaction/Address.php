<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\AddressServiceSoap;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;


class Address extends InteractionAbstract
{
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
     * @var array
     */
    protected $validAddressFields = [
        'line1' => 'string',
        'line2' => 'string',
        'line3' => 'string',
        'city' => 'string',
        'region' => 'string',
        'postalCode' => 'string',
        'country' => 'string',
        'taxRegionId' => 'integer',
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    /**
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     */
    public function __construct(
        ATConfigFactory $avaTaxConfigFactory,
        Config $config,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
        parent::__construct($avaTaxConfigFactory, $config);
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
            $type = $this->config->getLiveMode() ? self::API_PROFILE_NAME_PROD : self::API_PROFILE_NAME_DEV;
        }
        if (!isset($this->addressServiceSoap[$type])) {
            $this->addressServiceSoap[$type] =
                $this->addressServiceSoapFactory->create(['configurationName' => $type]);
        }
        return $this->addressServiceSoap[$type];
    }

    /**
     * Get an AvaTax address object with fields as specified in data
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
        $data = $this->filterAddressParams($data);
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

    /**
     * Remove all non-valid fields from data and convert incorrect typed data to the correctly typed data
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    protected function filterAddressParams($data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->validAddressFields)) {
                unset($data[$key]);
            } elseif (gettype($data[$key]) != $this->validAddressFields[$key]) {
                try {
                    settype($data[$key], $this->validAddressFields[$key]);
                } catch (\Exception $e) {
                    throw new LocalizedException(new Phrase('Could not convert "%1" to a "%2"', [
                        $key,
                        $this->validAddressFields[$key],
                    ]));
                }
            }
        }
        return $data;
    }
}