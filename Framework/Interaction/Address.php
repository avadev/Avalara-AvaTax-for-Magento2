<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\AddressServiceSoap;
use ClassyLlama\AvaTax\Helper\Validation;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
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
     * @var RegionCollection
     */
    protected $regionCollection = null;

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
        'line1' => ['type' => 'string', 'required' => true, 'length' => 50],
        'line2' => ['type' => 'string', 'length' => 50],
        'line3' => ['type' => 'string', 'length' => 50],
        'city' => ['type' => 'string', 'length' => 50],
        'region' => ['type' => 'string', 'length' => 3],
        'postalCode' => ['type' => 'string', 'required' => true, 'length' => 11],
        'country' => ['type' => 'string', 'length' => 2],
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
        AddressServiceSoapFactory $addressServiceSoapFactory,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->config = $config;
        $this->validation = $validation;
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
        $this->regionCollection = $regionCollectionFactory->create();
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
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data \Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|AddressModelInterface|array
     * @return \AvaTax\Address
     * @throws LocalizedException
     */
    public function getAddress($data)
    {
        switch (true) {
            case ($data instanceof \Magento\Customer\Api\Data\AddressInterface):
                $data = $this->convertCustomerAddressToAvaTaxAddress($data);
                break;
            case ($data instanceof \Magento\Quote\Api\Data\AddressInterface):
                $data = $this->convertQuoteAddressToAvaTaxAddress($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\OrderAddressInterface):
                $data = $this->convertOrderAddressToAvaTaxAddress($data);
                break;
            case ($data instanceof AddressModelInterface): // TODO: Decide if we need this still.  If we working with the Service Layer, this should never come up.
                $data = $this->convertAddressModelToAvaTaxAddress($data);
                break;
            case (!is_array($data)):
                throw new LocalizedException(new Phrase(
                    'Input parameter "$data" was not of a recognized/valid type: "%1".', [
                        gettype($data),
                ]));
        }

        if (isset($data['regionId'])) {
            $data['region'] = $this->getRegionById($data['regionId'])->getCode();
            unset($data['regionId']);
        }

        $data = $this->validation->validateData($data, $this->validAddressFields);
        return  $this->addressFactory->create($data);
    }

    /**
     * Converts Customer address into AvaTax compatible data array
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return array
     */
    protected function convertCustomerAddressToAvaTaxAddress(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $street = $address->getStreet();

        return $data = [
            'line1' => array_key_exists(0, $street) ? $street[0] : '',
            'line2' => array_key_exists(1, $street) ? $street[1] : '',
            'line3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $this->getRegionById($address->getRegionId())->getCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }

    /**
     * Converts Quote address into AvaTax compatible data array
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    protected function convertQuoteAddressToAvaTaxAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $street = $address->getStreet();

        return $data = [
            'line1' => array_key_exists(0, $street) ? $street[0] : '',
            'line2' => array_key_exists(1, $street) ? $street[1] : '',
            'line3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $this->getRegionById($address->getRegionId())->getCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }

    /**
     * Converts Order address into AvaTax compatible data array
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    protected function convertOrderAddressToAvaTaxAddress(\Magento\Sales\Api\Data\OrderAddressInterface $address)
    {
        $street = $address->getStreet();

        return $data = [
            'line1' => array_key_exists(0, $street) ? $street[0] : '',
            'line2' => array_key_exists(1, $street) ? $street[1] : '',
            'line3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $this->getRegionById($address->getRegionId())->getCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }



    /**
     * Converts address model into AvaTax compatible data array
     *
     * 3 address types implement this interface with two of them extending AddressAbstract
     * All three have these methods but since there is no comprehensive interface to rely on
     * this could break in the future.
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param AddressModelInterface $address
     * @return array
     */
    protected function convertAddressModelToAvaTaxAddress(AddressModelInterface $address)
    {
        return $data = [
            'line1' => $address->getStreetLine(1),
            'line2' => $address->getStreetLine(2),
            'line3' => $address->getStreetLine(3),
            'city' => $address->getCity(),
            'region' => $this->getRegionById($address->getRegionId())->getCode(),
            'postalCode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];
    }

    /**
     * Return region by id and if no region is found, throw an exception to prevent a fatal error so this can
     * be chained to call getCode
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $regionId
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     */
    protected function getRegionById($regionId)
    {
        $region = $this->regionCollection->getItemById($regionId);

        if (!($region instanceof Region)) {
            throw new LocalizedException(new Phrase(
                'Region "%1" was not found.', [
                $regionId,
            ]));
        }

        return $region;
    }
}