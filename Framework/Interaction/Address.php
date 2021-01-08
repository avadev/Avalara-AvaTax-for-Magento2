<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Framework\Interaction;

use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressInterfaceFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressInterfaceFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;

class Address
{
    /**
     * @var MetaDataObject
     */
    protected $metaDataObject = null;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var RegionCollection
     */
    protected $regionCollection = null;

    /**
     * @var CustomerAddressInterfaceFactory
     */
    protected $customerAddressFactory;

    /**
     * @var QuoteAddressInterfaceFactory
     */
    protected $quoteAddressFactory;

    /**
     * @var null|DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

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
        'line_1' => ['type' => 'string', 'length' => 50],
        'line_2' => ['type' => 'string', 'length' => 50],
        'line_3' => ['type' => 'string', 'length' => 50],
        'city' => ['type' => 'string', 'length' => 50], // Either city & region are required or postalCode is required.
        'region' => ['type' => 'string'], // Making postalCode required is easier but could be modified,
        'postal_code' => ['type' => 'string', 'required' => true, 'length' => 11], // if necessary.
        'country' => ['type' => 'string', 'length' => 2],
    ];

    /**
     * Address constructor.
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CustomerAddressInterfaceFactory $customerAddressFactory
     * @param QuoteAddressInterfaceFactory $quoteAddressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        MetaDataObjectFactory $metaDataObjectFactory,
        DataObjectFactory $dataObjectFactory,
        RegionCollectionFactory $regionCollectionFactory,
        CustomerAddressInterfaceFactory $customerAddressFactory,
        QuoteAddressInterfaceFactory $quoteAddressFactory,
        DataObjectHelper $dataObjectHelper,
        \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
    ) {
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->regionCollection = $regionCollectionFactory->create();
        $this->customerAddressFactory = $customerAddressFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->avaTaxLogger = $avaTaxLogger;
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
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     * @throws ValidationException
     */
    public function getAddress($data)
    {
        /** \Magento\Framework\DataObject $address */
        switch (true) {
            case ($data instanceof \Magento\Customer\Api\Data\AddressInterface
                || $data instanceof \Magento\Quote\Api\Data\AddressInterface):
                $address = $this->convertCustomerAddressToAvaTaxAddress($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\OrderAddressInterface):
                $address = $this->convertOrderAddressToAvaTaxAddress($data);
                break;
            case (is_array($data)):
                $address = $this->dataObjectFactory->create(['data' => $data]);
                break;
            default:
                throw new LocalizedException(__(
                    'Input parameter "$data" was not of a recognized/valid type: "%1".', [
                        gettype($data),
                ]));
        }

        if ($address->hasRegionId()) {
            $address->setRegion($this->getRegionCodeById($address->getRegionId()));
            $address->unsRegionId();
        }

        try {
            $validatedData = $this->metaDataObject->validateData($address->getData());
            $address->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating address: ' . $e->getMessage(), [
                'data' => var_export($address->getData(), true)
            ]);
            // Rethrow exception as if internal validation fails, don't send address to AvaTax
            throw $e;
        }

        return $address;
    }

    /**
     * Converts Customer address into AvaTax compatible data array
     *
     * @param \Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface $address
     * @return \Magento\Framework\DataObject
     */
    public function convertCustomerAddressToAvaTaxAddress($address)
    {
        $street = $address->getStreet();

        $data = [
            'line_1' => array_key_exists(0, $street) ? $street[0] : '',
            'line_2' => array_key_exists(1, $street) ? $street[1] : '',
            'line_3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $this->getRegionCodeById($address->getRegionId()),
            'postal_code' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];

        /** @var \Magento\Framework\DataObject $address */
        $address = $this->dataObjectFactory->create(['data' => $data]);

        return $address;
    }

    /**
     * Converts Order address into AvaTax compatible data array
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return \Magento\Framework\DataObject
     */
    public function convertOrderAddressToAvaTaxAddress(OrderAddressInterface $address)
    {
        $street = $address->getStreet();

        $data = [
            'line_1' => array_key_exists(0, $street) ? $street[0] : '',
            'line_2' => array_key_exists(1, $street) ? $street[1] : '',
            'line_3' => array_key_exists(2, $street) ? $street[2] : '',
            'city' => $address->getCity(),
            'region' => $this->getRegionCodeById($address->getRegionId()),
            'postal_code' => $address->getPostcode(),
            'country' => $address->getCountryId(),
        ];

        /** @var \Magento\Framework\DataObject $address */
        $address = $this->dataObjectFactory->create(['data' => $data]);

        return $address;
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

        // Per Github issue #4, the \Magento\Framework\Api\DataObjectHelper::_setDataValues method chokes on the
        // custom_attributes array, so remove it and set it after the data has been mapped
        $customAttributes = false;
        if (isset($customerAddressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
            && is_array($customerAddressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
        ) {
            $customAttributes = $customerAddressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES];
            unset($customerAddressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]);
        }

        $addressDataObject = $this->customerAddressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $customerAddressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );

        if ($customAttributes) {
            $addressDataObject->setCustomAttributes($customAttributes);
        }

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
     * @param \Magento\Framework\DataObject $address
     * @param CustomerAddressInterface $originalAddress
     * @return null|CustomerAddressInterface
     */
    public function convertAvaTaxValidAddressToCustomerAddress(
        $address,
        CustomerAddressInterface $originalAddress
    ) {
        $street = [];
        if ($address->hasLine1()) {
            $street[] = $address->getLine1();
        }
        if ($address->hasLine2()) {
            $street[] = $address->getLine2();
        }
        if ($address->hasLine3()) {
            $street[] = $address->getLine3();
        }
        // Not using line 4, as it returns a concatenation of city, state, and zip (e.g., BAINBRIDGE IS WA 98110-2450)

        $region = $this->getRegionByCodeAndCountry($address->getRegion(), $address->getCountry());
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
     * @param \Magento\Framework\DataObject $address
     * @param QuoteAddressInterface $originalAddress
     * @return QuoteAddressInterface
     */
    public function convertAvaTaxValidAddressToQuoteAddress(
        $address,
        \Magento\Quote\Api\Data\AddressInterface $originalAddress
    ) {
        $street = [];
        if ($address->hasLine1()) {
            $street[] = $address->getLine1();
        }
        if ($address->hasLine2()) {
            $street[] = $address->getLine2();
        }
        if ($address->hasLine3()) {
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

        $region = $this->getRegionByCodeAndCountry($address->getRegion(), $address->getCountry());
        if (!is_null($region)) {
            $data[QuoteAddressInterface::KEY_REGION_ID] = $region->getId();
            $data[QuoteAddressInterface::KEY_REGION] = $region->getName();
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

        // Countries with free-form regions can't be loaded from the database, so just return it as the code
        if (!is_numeric($regionId)) {
            return $regionId;
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
    protected function getRegionByCodeAndCountry($regionCode, $countryCode)
    {

        /* @var $region \Magento\Framework\DataObject */
        foreach ($this->regionCollection as $region) {
            if ($region->getCode() == $regionCode && $region->getCountryId() == $countryCode) {
                return $region;
            }
        }

        return null;
    }
}
