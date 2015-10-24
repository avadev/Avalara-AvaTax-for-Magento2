<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\DetailLevel;
use AvaTax\DocumentType;
use AvaTax\GetTaxRequest;
use AvaTax\GetTaxRequestFactory;
use AvaTax\TaxServiceSoap;
use AvaTax\TaxServiceSoapFactory;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Zend\Filter\DateTimeFormatter;

class Tax
{
    /**
     * @var Address
     */
    protected $address = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var TaxServiceSoapFactory
     */
    protected $taxServiceSoapFactory = [];

    /**
     * @var GetTaxRequestFactory
     */
    protected $getTaxRequestFactory = null;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository = null;

    /**
     * @var TaxClassRepositoryInterface
     */
    protected $taxClassRepository = null;

    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter = null;

    /**
     * @var TaxServiceSoap[]
     */
    protected $taxServiceSoap = [];

    /**
     * List of types that can be used with setType
     *
     * @var array
     */
    protected $simpleTypes = ['boolean', 'integer', 'string', 'float', 'null'];

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.  If any fields are added or removed, the same should be done in getGetTaxRequest.
     *
     * @var array
     */
    protected $validDataFields = [
        'store_id' => ['type' => 'integer'],
        'business_identification_no' => ['type' => 'string', 'length' => 25],
        'commit' => ['type' => 'boolean'],
        'company_code' => ['type' => 'string', 'length' => 25],
        'currency_code' => ['type' => 'string', 'length' => 3],
        'customer_code' => ['type' => 'string', 'length' => 50, 'required' => true],
        'customer_usage_type' => ['type' => 'string', 'length' => 25],
        'destination_address' => ['type' => 'object', 'class' => '\AvaTax\Address', 'required' => true],
        'detail_level' => ['options' => ['Document', 'Diagnostic', 'Line', 'Summary', 'Tax']],
        'discount' => ['type' => 'float'],
        'doc_code' => ['type' => 'string', 'length' => 50],
        'doc_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/', 'required' => true],
        'doc_type' => ['type' => 'string', 'options' =>
            ['SalesOrder', 'SalesInvoice', 'PurchaseOrder', 'PurchaseInvoice', 'ReturnOrder', 'ReturnInvoice'],
            'required' => true,
        ],
        'exchange_rate' => ['type' => 'float'],
        'exchange_rate_eff_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'exemption_no' => ['type' => 'string', 'length' => 25],
        'lines' => [
            'type' => 'array',
            'length' => 15000,
            'subtype' => ['type' => 'object', 'class' => '\AvaTax\Line'],
            'required' => true,
        ],
        'location_code' => ['type' => 'string', 'length' => 50, 'required' => true],
        'origin_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'payment_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'purchase_order_number' => ['type' => 'string', 'length' => 50],
        'reference_code' => ['type' => 'string', 'length' => 50],
        'salesperson_code' => ['type' => 'string', 'length' => 25],
        'tax_override' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
    ];

    public function __construct(
        Address $address,
        Config $config,
        TaxServiceSoapFactory $taxServiceSoapFactory,
        GetTaxRequestFactory $getTaxRequestFactory,
        GroupRepositoryInterface $groupRepository,
        TaxClassRepositoryInterface $taxClassRepository,
        DateTimeFormatter $dateTimeFormatter
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->taxServiceSoapFactory = $taxServiceSoapFactory;
        $this->getTaxRequestFactory = $getTaxRequestFactory;
        $this->groupRepository = $groupRepository;
        $this->taxClassRepository = $taxClassRepository;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * Get tax service by type and cache instances by type to avoid duplicate instantiation
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param null $type
     * @return TaxServiceSoap
     */
    public function getTaxService($type = null)
    {
        if (is_null($type)) {
            $type = $this->config->getLiveMode() ? Config::API_PROFILE_NAME_PROD : Config::API_PROFILE_NAME_DEV;
        }
        if (!isset($this->taxServiceSoap[$type])) {
            $this->taxServiceSoap[$type] =
                $this->taxServiceSoapFactory->create(['configurationName' => $type]);
        }
        return $this->taxServiceSoap[$type];
    }

    /**
     * Remove all non-valid fields from data, convert incorrect typed data to the correctly typed data,
     * validate length, and validate existence
     * TODO: Simplify this if possible
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    protected function filterDataParams(array $data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->validDataFields) || !isset($this->validDataFields[$key]['type'])) {
                unset($data[$key]);
            } elseif ('array' == $this->validDataFields[$key]['type']) {
                if (gettype($data[$key]) != $this->validDataFields[$key]['type']) {
                    unset($data[$key]);
                    continue;
                }
                if (isset($this->validDataFields[$key]['subtype'])) {
                    foreach ($data['key'] as $subKey => $subItem) {
                        // If the type of each subitem is not correct try to change it (if logical to do so)
                        if (gettype($subItem) != $this->validDataFields[$key]['subtype']['type']) {
                            if (in_array($this->validDataFields[$key]['type'], $this->simpleTypes)) {
                                try {
                                    settype($data[$key], $this->validDataFields[$key]['type']);
                                } catch (\Exception $e) {
                                    throw new LocalizedException(new Phrase('Could not convert "%1[%2]" to a "%3"', [
                                        $key,
                                        $subKey,
                                        $this->validDataFields[$key],
                                    ]));
                                }
                            } else { // Otherwise remove it
                                unset($data[$key][$subKey]);
                                continue;
                            }
                        // If the type of the subitem is correct but is object, enforce that it is of the correct class
                        } elseif ('object' == $this->validDataFields[$key]['subtype']['type'] &&
                            isset($this->validDataFields[$key]['subtype']['class'])) {
                            if (!($subItem instanceof $this->validDataFields[$key]['subtype']['class'])) {
                                unset($data[$key][$subKey]);
                                continue;
                            }
                        }
                    }
                }
            } elseif ('object' == $this->validDataFields[$key]['type'] &&
                isset($this->validDataFields[$key]['class'])) {
                if (!($subItem instanceof $this->validDataFields[$key]['class'])) {
                    unset($data[$key]);
                    continue;
                }
            } elseif (gettype($data[$key]) != $this->validDataFields[$key]['type'] &&
                in_array($this->validDataFields[$key]['type'], $this->simpleTypes)) {
                try {
                    settype($data[$key], $this->validDataFields[$key]['type']);
                } catch (\Exception $e) {
                    throw new LocalizedException(new Phrase('Could not convert "%1" to a "%2"', [
                        $key,
                        $this->validDataFields[$key],
                    ]));
                }
            }
        }
        return $data;
    }

    /**
     * Determines whether tax should be committed or not
     * TODO: Add functionality to determine whether an order should be committed or not, look at previous module and maybe do something around order statuses
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    protected function shouldCommit(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return false;
    }

    /**
     * Return customer code according to the admin configured format
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $name
     * @param $email
     * @param $id
     * @return string
     */
    protected function getCustomerCode($name, $email, $id)
    {
        switch ($this->config->getCustomerCodeFormat()) {
            case Config::CUSTOMER_FORMAT_OPTION_EMAIL:
                return $email;
                break;
            case Config::CUSTOMER_FORMAT_OPTION_NAME_ID:
                return sprintf(Config::CUSTOMER_FORMAT_NAME_ID);
                break;
            case Config::CUSTOMER_FORMAT_OPTION_ID:
                return $id;
                break;
            default:
                return $email;
                break;
        }
    }

    /**
     * Return the exchange rate between base currency and destination currency code
     * TODO: Calculate the exchange rate from the system exchange rates
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $baseCurrencyCode
     * @param $convertCurrencyCode
     * @return double
     */
    protected function getExchangeRate($baseCurrencyCode, $convertCurrencyCode)
    {
        return 1.00;
    }

    /**
     * Convert an order into data to be used in some kind of tax request
     * TODO: Find out what happens if Business Identification Number is passed and we do not want to consider VAT.  Probably add config field to allow user to not consider VAT.
     * TODO: Use Tax Class to get customer usage code
     * TODO: Decide if we need to ever pass an exemption no[number] for an order.  Anything passed as an exemption no will cause the whole order to be exempt.  Use very carefully.
     * TODO: Make sure discount lines up proportionately with how Magento does it and if not, figure out if there is another way to do it.
     * TODO: Create and inject interaction line object as interactionLine
     * TODO: Account for non item based lines according to documentation
     * TODO: Look into whether M1 module is sending payment date and if not, ask under what circumstances it should be sent
     * TODO: Determine how to get parent increment id if one is set on order and set it on reference code
     * TODO: Determine what circumstance tax override will need to be set and set in order in those cases
     * TODO: Determine what salesperson code to pass if any
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function convertOrderToData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $customerGroupId = $this->groupRepository->getById($order->getCustomerGroupId())->getTaxClassId();
        $taxClass = $this->taxClassRepository->get($customerGroupId);

        $lines = [];
        foreach ($order->getItems() as $item) {
            $lines[] = $this->interactionLine->getLine($item);
        }

        return [
            'store_id' => $order->getStoreId(),
            'commit' => $this->shouldCommit($order),
            'currency_code' => $order->getOrderCurrencyCode(),
            'customer_code' => $this->getCustomerCode(
                $order->getCustomerFirstname(),
                $order->getCustomerEmail(),
                $order->getCustomerId()
            ),
            'customer_usage_type' => null,//$taxClass->,
            'destination_address' => $this->address->getAddress($order->getShippingAddress()),
            'discount' => $order->getDiscountAmount(),
            'doc_code' => $order->getIncrementId(),
            'doc_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter($order->getCreatedAt()),
            'exchange_rate' => $this->getExchangeRate($order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()),
            'exchange_rate_eff_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter(date()),
            'exemption_no' => null,//$order->getTaxExemptionNumber(),
            'lines' => $lines,
            'payment_date' => null,
            'purchase_order_number' => $order->getIncrementId(),
            'reference_code' => null, // Most likely only set on credit memos or order edits
            'salesperson_code' => null,
            'tax_override' => null,
        ];

    }

    /**
     * Ensures that all required exists and that it is logically valid
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return mixed
     */
    public function validateData(array $data)
    {
        return $data;
    }

    /**
     * Creates and returns a populated getTaxRequest
     * TODO: Determine what the appropriate DetailLevel is, possible make configurable, check M1 module
     * TODO: Handle for the case where 'store_id' attribute of $data is not set
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     */
    public function getGetTaxRequest($data)
    {
        switch (true) {
            case ($data instanceof \Magento\Sales\Api\Data\OrderInterface):
                $data = $this->convertOrderToData($data);
                break;
            case ($data instanceof \Magento\Quote\Api\Data\CartInterface):
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceInterface):
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoInterface):
                break;
            case (!is_array($data)):
                return false;
                break;
        }
        $data = array_merge(
            [
                'business_identification_no' => $this->config->getBusinessIdentificationNumber($data['store_id']),
                'company_code' => $this->config->getCompanyCode($data['store_id']),
                'detail_level' => DetailLevel::$Document,
                'doc_type' => DocumentType::$PurchaseInvoice,
                'origin_address' => $this->address->getAddress($this->config->getOriginAddress($data['store_id'])),
            ],
            $data
        );

        $data = $this->filterDataParams($data);
        $data = $this->validateData($data);
        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->getTaxRequestFactory->create();

        // Set any data elements that exist on the getTaxRequest
        if (isset($data['business_identification_no'])) {
            $getTaxRequest->setBusinessIdentificationNo($data['business_identification_no']);
        }
        if (isset($data['commit'])) {
            $getTaxRequest->setCommit($data['commit']);
        }
        if (isset($data['company_code'])) {
            $getTaxRequest->setCompanyCode($data['company_code']);
        }
        if (isset($data['currency_code'])) {
            $getTaxRequest->setCurrencyCode($data['currency_code']);
        }
        if (isset($data['customer_code'])) {
            $getTaxRequest->setCustomerCode($data['customer_code']);
        }
        if (isset($data['customer_usage_type'])) {
            $getTaxRequest->setCustomerUsageType($data['customer_usage_type']);
        }
        if (isset($data['destination_address'])) {
            $getTaxRequest->setDestinationAddress($data['destination_address']);
        }
        if (isset($data['detail_level'])) {
            $getTaxRequest->setDetailLevel($data['detail_level']);
        }
        if (isset($data['discount'])) {
            $getTaxRequest->setDiscount($data['discount']);
        }
        if (isset($data['doc_code'])) {
            $getTaxRequest->setDocCode($data['doc_code']);
        }
        if (isset($data['doc_date'])) {
            $getTaxRequest->setDocDate($data['doc_date']);
        }
        if (isset($data['doc_type'])) {
            $getTaxRequest->setDocType($data['doc_type']);
        }
        if (isset($data['exchange_rate'])) {
            $getTaxRequest->setExchangeRate($data['exchange_rate']);
        }
        if (isset($data['exchange_rate_eff_date'])) {
            $getTaxRequest->setExchangeRateEffDate($data['exchange_rate_eff_date']);
        }
        if (isset($data['exemption_no'])) {
            $getTaxRequest->setExemptionNo($data['exemption_no']);
        }
        if (isset($data['lines'])) {
            $getTaxRequest->setLines($data['lines']);
        }
        if (isset($data['location_code'])) {
            $getTaxRequest->setLocationCode($data['location_code']);
        }
        if (isset($data['origin_address'])) {
            $getTaxRequest->setOriginAddress($data['origin_address']);
        }
        if (isset($data['payment_date'])) {
            $getTaxRequest->setPaymentDate($data['payment_date']);
        }
        if (isset($data['purchase_order_number'])) {
            $getTaxRequest->setPurchaseOrderNo($data['purchase_order_number']);
        }
        if (isset($data['reference_code'])) {
            $getTaxRequest->setReferenceCode($data['reference_code']);
        }
        if (isset($data['salesperson_code'])) {
            $getTaxRequest->setSalespersonCode($data['salesperson_code']);
        }
        if (isset($data['tax_override'])) {
            $getTaxRequest->setTaxOverride($data['tax_override']);
        }

        return $getTaxRequest;
    }
}