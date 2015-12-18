<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
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
use Magento\Framework\Api\DataObjectHelper;
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
     * @var CustomerAddressInterfaceFactory
     */
    protected $customerAddressFactory = null;

    /**
     * @var QuoteAddressInterfaceFactory
     */
    protected $quoteAddressFactory = null;

    /**
     * @var OrderAddressInterfaceFactory
     */
    protected $orderAddressFactory = null;

    /**
     * @var AddressServiceSoap[]
     */
    protected $addressServiceSoap = [];

    /**
     * Validation based on API documentation found here:
     * http://developer.avalara.com/wp-content/apireference/master/?php#validate-request58
     *
     * @var array
     */
    protected $validAddressFields = [
        /*
         * The AvaTax API defines Line1 as required, however in implementation it is not required. We can't require
         * it here, as we need to be able to calculate taxes from the cart page using Postal Code, Region, and Country.
         */
        'line1' => ['type' => 'string', 'length' => 50],
        'line2' => ['type' => 'string', 'length' => 50],
        'line3' => ['type' => 'string', 'length' => 50],
        'city' => ['type' => 'string', 'length' => 50], // Either city & region are required or postalCode is required.
        'region' => ['type' => 'string', 'length' => 3], // Making postalCode required is easier but could be modified,
        'postalCode' => ['type' => 'string', 'required' => true, 'length' => 11], // if necessary.
        'country' => ['type' => 'string', 'length' => 2],
        'taxRegionId' => ['type' => 'integer'],
        'latitude' => ['type' => 'string'],
        'longitude' => ['type' => 'string'],
    ];

    /**
     * @var null|DataObjectHelper
     */
    protected $dataObjectHelper = null;

    /**
     * Address constructor.
     * @param Config $config
     * @param Validation $validation
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CustomerAddressInterfaceFactory $customerAddressFactory
     * @param QuoteAddressInterfaceFactory $quoteAddressFactory
     * @param OrderAddressInterfaceFactory $orderAddressFactory
     * @internal param ATConfigFactory $avaTaxConfigFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Config $config,
        Validation $validation,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory,
        RegionCollectionFactory $regionCollectionFactory,
        CustomerAddressInterfaceFactory $customerAddressFactory,
        QuoteAddressInterfaceFactory $quoteAddressFactory,
        OrderAddressInterfaceFactory $orderAddressFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->config = $config;
        $this->validation = $validation;
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
        $this->regionCollection = $regionCollectionFactory->create();
        $this->customerAddressFactory = $customerAddressFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
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
     *
     * Note: AvaTax only allows 3 street fields according to the API documentation.  The customer/address/street_lines
     * allows the admin to create fields for 1 to 4 street lines.  This configuration is currently disabled in
     * Magento/CustomerCustomAttributes/etc/adminhtml/system.xml.  As a result not currently doing anything with this.
     * Likely no special consideration since the code is already sending all addresses (up to 3) to AvaTax if present.
     *
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
    public function convertCustomerAddressToAvaTaxAddress(CustomerAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
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
    public function convertQuoteAddressToAvaTaxAddress(QuoteAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
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
    public function convertOrderAddressToAvaTaxAddress(OrderAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
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
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param QuoteAddressInterface $quoteAddress
     * @param CustomerAddressInterface $customerAddress
     * @return null|CustomerAddressInterface
     */
    public function copyQuoteAddressToCustomerAddress(
        QuoteAddressInterface $quoteAddress,
        CustomerAddressInterface $customerAddress
    ) {
        $customerAddress->setRegionId($quoteAddress->getRegionId());
        $customerAddress->setCountryId($quoteAddress->getCountryId());
        $customerAddress->setStreet($quoteAddress->getStreet());
        $customerAddress->setPostcode($quoteAddress->getPostcode());
        $customerAddress->setCity($quoteAddress->getCity());

        $customerAddressData = $this->getCustomerAddressData($customerAddress);

        $customerAddressDataWithRegion = [];
        $customerAddressDataWithRegion['region']['region'] = $quoteAddress->getRegion();
        $customerAddressDataWithRegion['region']['region_code'] = $quoteAddress->getRegionCode();
        if ($customerAddressData['region_id']) {
            $customerAddressDataWithRegion['region']['region_id'] = $quoteAddress->getRegionId();
        }

        $customerAddressData = array_merge($customerAddressData, $customerAddressDataWithRegion);
        $addressDataObject = $this->customerAddressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $customerAddressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );

        return $addressDataObject;
    }

    /**
     * This method is necessary because a customer address does not have a getData() method to retrieve all the address
     * fields.
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param CustomerAddressInterface $customerAddress
     * @return array
     */
    protected function getCustomerAddressData(CustomerAddressInterface $customerAddress){
        $customerAddressData = [
            CustomerAddressInterface::COUNTRY_ID => $customerAddress->getCountryId(),
            CustomerAddressInterface::STREET => $customerAddress->getStreet(),
            CustomerAddressInterface::POSTCODE => $customerAddress->getPostcode(),
            CustomerAddressInterface::CITY => $customerAddress->getCity(),
            CustomerAddressInterface::COMPANY => $customerAddress->getCompany(),
            CustomerAddressInterface::CUSTOM_ATTRIBUTES => $customerAddress->getCustomAttributes(),
            CustomerAddressInterface::CUSTOMER_ID => $customerAddress->getCustomerId(),
            CustomerAddressInterface::EXTENSION_ATTRIBUTES_KEY => $customerAddress->getExtensionAttributes(),
            CustomerAddressInterface::FAX => $customerAddress->getFax(),
            CustomerAddressInterface::FIRSTNAME => $customerAddress->getFirstname(),
            CustomerAddressInterface::ID => $customerAddress->getId(),
            CustomerAddressInterface::LASTNAME => $customerAddress->getLastname(),
            CustomerAddressInterface::MIDDLENAME => $customerAddress->getMiddlename(),
            CustomerAddressInterface::PREFIX => $customerAddress->getPrefix(),
            CustomerAddressInterface::REGION => $customerAddress->getRegion(),
            CustomerAddressInterface::REGION_ID => $customerAddress->getRegionId(),
            CustomerAddressInterface::STREET => $customerAddress->getStreet(),
            CustomerAddressInterface::SUFFIX => $customerAddress->getSuffix(),
            CustomerAddressInterface::TELEPHONE => $customerAddress->getTelephone(),
            CustomerAddressInterface::VAT_ID => $customerAddress->getVatId(),
            CustomerAddressInterface::DEFAULT_BILLING => $customerAddress->isDefaultBilling(),
            CustomerAddressInterface::DEFAULT_SHIPPING => $customerAddress->isDefaultShipping()
        ];

        return $customerAddressData;
    }

    /**
     * Convert ValidAddress to CustomerAddressInterface
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\ValidAddress $address
     * @param CustomerAddressInterface $originalAddress
     * @return null|CustomerAddressInterface
     */
    public function convertAvaTaxValidAddressToCustomerAddress(
        \AvaTax\ValidAddress $address,
        CustomerAddressInterface $originalAddress
    ) {
        $street = [];
        if ($address->getLine1()) {
            $street[] = $address->getLine1();
        }
        if ($address->getLine2()) {
            $street[] = $address->getLine2();
        }
        if ($address->getLine3()) {
            $street[] = $address->getLine3();
        }
        // Not using line 4, as it returns a concatenation of city, state, and zipcode (e.g., BAINBRIDGE IS WA 98110-2450)

        $region = $this->getRegionByCode($address->getRegion());
        if (is_null($region)) {
            return null;
        }

        $customerAddressData = $this->getCustomerAddressData($originalAddress);

        // Get data from original address so that information like name and telephone will be preserved
        $data = array_merge($customerAddressData, [
            CustomerAddressInterface::REGION => $region->getName(),
            CustomerAddressInterface::REGION_ID => $region->getId(),
            CustomerAddressInterface::COUNTRY_ID => $address->getCountry(),
            CustomerAddressInterface::STREET => $street,
            CustomerAddressInterface::POSTCODE => $address->getPostalCode(),
            CustomerAddressInterface::CITY => $address->getCity(),
        ]);

        return $this->customerAddressFactory->create(['data' => $data]);
    }

    /**
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\ValidAddress $address
     * @return array
     */
    public function convertAvaTaxValidAddressToArray(\AvaTax\ValidAddress $address)
    {
        return [
            'region' => $address->getRegion(),
            'country' => $address->getCountry(),
            'line1' => $address->getLine1(),
            'line2' => $address->getLine2(),
            'line3' => $address->getLine3(),
            'postalCode' => $address->getPostalCode(),
            'city' => $address->getCity(),
        ];
    }

    /**
     * Convert ValidAddress to QuoteAddressInterface
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\ValidAddress $address
     * @param QuoteAddressInterface $originalAddress
     * @return QuoteAddressInterface
     */
    public function convertAvaTaxValidAddressToQuoteAddress(
        \AvaTax\ValidAddress $address,
        \Magento\Quote\Api\Data\AddressInterface $originalAddress
    ) {
        $street = [];
        if ($address->getLine1()) {
            $street[] = $address->getLine1();
        }
        if ($address->getLine2()) {
            $street[] = $address->getLine2();
        }
        if ($address->getLine3()) {
            $street[] = $address->getLine3();
        }
        // Not using line 4, as it returns a concatenation of city, state, and zipcode (e.g., BAINBRIDGE IS WA 98110-2450)

        // Get data from original address so that information like name and telephone will be preserved
        $data = array_merge($originalAddress->getData(), [
            QuoteAddressInterface::KEY_COUNTRY_ID => $address->getCountry(),
            QuoteAddressInterface::KEY_REGION_CODE => $address->getRegion(),
            QuoteAddressInterface::KEY_STREET => $street,
            QuoteAddressInterface::KEY_POSTCODE => $address->getPostalCode(),
            QuoteAddressInterface::KEY_CITY => $address->getCity(),
        ]);

        $region = $this->getRegionByCode($address->getRegion());
        if (!is_null($region)) {
            $data[QuoteAddressInterface::KEY_REGION_ID] = $region->getId();
            $data[QuoteAddressInterface::KEY_REGION] = $region;
        }
        return $this->quoteAddressFactory->create(['data' => $data]);
    }

    /**
     * Convert ValidAddress to OrderAddressInterface
     * TODO: Remove this method if it ends up not getting used
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\ValidAddress $address
     * @param OrderAddressInterface $originalAddress
     * @return null|OrderAddressInterface
     */
    public function convertAvaTaxValidAddressToOrderAddress(
        \AvaTax\ValidAddress $address,
        \Magento\Sales\Api\Data\OrderAddressInterface $originalAddress
    ) {
        $street = [];
        if ($address->getLine1()) {
            $street[] = $address->getLine1();
        }
        if ($address->getLine2()) {
            $street[] = $address->getLine2();
        }
        if ($address->getLine3()) {
            $street[] = $address->getLine3();
        }
        // Not using line 4, as it returns a concatenation of city, state, and zipcode (e.g., BAINBRIDGE IS WA 98110-2450)

        $region = $this->getRegionByCode($address->getRegion());
        if (is_null($region)) {
            return null;
        }

        // Get data from original address so that information like name and telephone will be preserved
        $data = array_merge($originalAddress->getData(), [
            OrderAddressInterface::REGION => $region,
            OrderAddressInterface::REGION_ID => $region->getId(),
            OrderAddressInterface::COUNTRY_ID => $address->getCountry(),
            OrderAddressInterface::STREET => $street,
            OrderAddressInterface::POSTCODE => $address->getPostalCode(),
            OrderAddressInterface::CITY => $address->getCity(),
        ]);

        return $this->orderAddressFactory->create(['data' => $data]);
    }

    /**
     * Converts address model into AvaTax compatible data array
     *
     * 3 address types implement this interface with two of them extending AddressAbstract
     * All three have the methods called in this method but since there is no comprehensive interface to rely on
     * this could break in the future.
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param AddressModelInterface $address
     * @return array
     */
    public function convertAddressModelToAvaTaxAddress(AddressModelInterface $address)
    {
        return [
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
     * be chained to call getCode or other method by wrapping in a try/catch block.
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

    /**
     * Return region by code and if no region is found
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $regionCode
     * @return \Magento\Framework\DataObject|null
     */
    protected function getRegionByCode($regionCode)
    {

        /* @var $region \Magento\Framework\DataObject */
        foreach ($this->regionCollection as $region) {
            if ($region->getCode() == $regionCode) {
                return $region;
            }
        }

        return null;
    }
}