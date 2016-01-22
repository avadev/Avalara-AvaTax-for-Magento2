<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;
use AvaTax\AddressServiceSoap;
use ClassyLlama\AvaTax\Helper\Config;
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
     * @var MetaDataObject
     */
    protected $metaDataObject = null;

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
     * @var null|DataObjectHelper
     */
    protected $dataObjectHelper = null;

    /**
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

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
    public static $validFields = [
        /*
         * The AvaTax API defines Line1 as required, however in implementation it is not required. We can't require
         * it here, as we need to be able to calculate taxes from the cart page using Postal Code, Region, and Country.
         */
        'Line1' => ['type' => 'string', 'length' => 50],
        'Line2' => ['type' => 'string', 'length' => 50],
        'Line3' => ['type' => 'string', 'length' => 50],
        'City' => ['type' => 'string', 'length' => 50], // Either city & region are required or postalCode is required.
        'Region' => ['type' => 'string', 'length' => 3], // Making postalCode required is easier but could be modified,
        'PostalCode' => ['type' => 'string', 'required' => true, 'length' => 11], // if necessary.
        'Country' => ['type' => 'string', 'length' => 2],
        'TaxRegionId' => ['type' => 'integer', 'useInCacheKey' => false],
        'Latitude' => ['type' => 'string', 'useInCacheKey' => false],
        'Longitude' => ['type' => 'string', 'useInCacheKey' => false],
    ];

    /**
     * Address constructor.
     * @param Config $config
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CustomerAddressInterfaceFactory $customerAddressFactory
     * @param QuoteAddressInterfaceFactory $quoteAddressFactory
     * @param OrderAddressInterfaceFactory $orderAddressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        Config $config,
        MetaDataObjectFactory $metaDataObjectFactory,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory,
        RegionCollectionFactory $regionCollectionFactory,
        CustomerAddressInterfaceFactory $customerAddressFactory,
        QuoteAddressInterfaceFactory $quoteAddressFactory,
        OrderAddressInterfaceFactory $orderAddressFactory,
        DataObjectHelper $dataObjectHelper,
        \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
    ) {
        $this->config = $config;
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
        $this->regionCollection = $regionCollectionFactory->create();
        $this->customerAddressFactory = $customerAddressFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * Get address service by type and cache instances by type to avoid duplicate instantiation
     *
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
     * @param $data \Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array
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
            case (!is_array($data)):
                throw new LocalizedException(__(
                    'Input parameter "$data" was not of a recognized/valid type: "%1".', [
                        gettype($data),
                ]));
        }

        if (isset($data['RegionId'])) {
            $data['Region'] = $this->getRegionCodeById($data['RegionId']);
            unset($data['RegionId']);
        }

        try {
            $data = $this->metaDataObject->validateData($data);
        } catch (MetaData\ValidationException $e) {
            $this->avaTaxLogger->error('Error validating address: ' . $e->getMessage(), [
                'data' => var_export($data, true)
            ]);
            // Rethrow exception as if internal validation fails, don't send address to AvaTax
            throw $e;
        }

        $address = $this->addressFactory->create();
        return $this->populateAddress($data, $address);
    }

    /**
     * Converts Customer address into AvaTax compatible data array
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return array
     */
    public function convertCustomerAddressToAvaTaxAddress(CustomerAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
            'Line1' => array_key_exists(0, $street) ? $street[0] : '',
            'Line2' => array_key_exists(1, $street) ? $street[1] : '',
            'Line3' => array_key_exists(2, $street) ? $street[2] : '',
            'City' => $address->getCity(),
            'Region' => $this->getRegionCodeById($address->getRegionId()),
            'PostalCode' => $address->getPostcode(),
            'Country' => $address->getCountryId(),
        ];
    }

    /**
     * Converts Quote address into AvaTax compatible data array
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    public function convertQuoteAddressToAvaTaxAddress(QuoteAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
            'Line1' => array_key_exists(0, $street) ? $street[0] : '',
            'Line2' => array_key_exists(1, $street) ? $street[1] : '',
            'Line3' => array_key_exists(2, $street) ? $street[2] : '',
            'City' => $address->getCity(),
            'Region' => $this->getRegionCodeById($address->getRegionId()),
            'PostalCode' => $address->getPostcode(),
            'Country' => $address->getCountryId(),
        ];
    }

    /**
     * Converts Order address into AvaTax compatible data array
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    public function convertOrderAddressToAvaTaxAddress(OrderAddressInterface $address)
    {
        $street = $address->getStreet();

        return [
            'Line1' => array_key_exists(0, $street) ? $street[0] : '',
            'Line2' => array_key_exists(1, $street) ? $street[1] : '',
            'Line3' => array_key_exists(2, $street) ? $street[2] : '',
            'City' => $address->getCity(),
            'Region' => $this->getRegionCodeById($address->getRegionId()),
            'PostalCode' => $address->getPostcode(),
            'Country' => $address->getCountryId(),
        ];
    }

    /**
     * Copies a quote address to a customer address
     *
     * Ths function serves to create a customer address from a quote address because when a user is logged in and they
     * go to checkout, if they select an existing customer address the checkout module will retrieve that customer
     * address to set as the quote address. Rather that update the quote address every time at checkout, this module
     * will update the customer address (if the user selects the valid address) so that customer address only needs
     * to be validated once. To do this, the quote address must be converted to a customer address so the validated
     * address can be saved to the quote address.
     *
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
        // Not using line 4, as it returns a concatenation of city, state, and zip (e.g., BAINBRIDGE IS WA 98110-2450)

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
     * Convert ValidAddress to QuoteAddressInterface
     *
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
        // Not using line 4, as it returns a concatenation of city, state, and zip (e.g., BAINBRIDGE IS WA 98110-2450)

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
     * Return region code by id
     *
     * @param $regionId
     * @return string|null
     * @throws LocalizedException
     */
    protected function getRegionCodeById($regionId)
    {
        if (!$regionId) {
            return null;
        }

        /** @var \Magento\Directory\Model\Region $region */
        $region = $this->regionCollection->getItemById($regionId);

        if (!($region instanceof Region)) {
            throw new LocalizedException(__(
                'Region "%1" was not found.', [
                $regionId,
            ]));
        }

        return $region->getCode();
    }

    /**
     * Return region by code and if no region is found
     *
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

    /**
     * Map data array to methods in GetTaxRequest object
     *
     * @param array $data
     * @param \AvaTax\Address $address
     * @return \AvaTax\Address
     */
    protected function populateAddress(array $data, \AvaTax\Address $address)
    {
        // Set any data elements that exist on the getTaxRequest
        foreach ($data as $key => $datum) {
            $methodName = 'set' . $key;
            if (method_exists($address, $methodName)) {
                $address->$methodName($datum);
            }
        }
        return $address;
    }
}
