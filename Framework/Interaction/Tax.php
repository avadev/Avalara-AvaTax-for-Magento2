<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\DetailLevel;
use AvaTax\DocumentType;
use AvaTax\GetTaxRequest;
use AvaTax\GetTaxRequestFactory;
use AvaTax\TaxServiceSoap;
use AvaTax\TaxServiceSoapFactory;
use ClassyLlama\AvaTax\Helper\Validation;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
     * @var Validation
     */
    protected $validation = null;

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
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Line
     */
    protected $interactionLine = null;

    /**
     * @var TaxServiceSoap[]
     */
    protected $taxServiceSoap = [];

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation = null;

    /**
     * List of types that we want to be used with setType
     *
     * @var array
     */
    protected $simpleTypes = ['boolean', 'integer', 'string', 'float'];

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
        // Company Code is not required by the the API, but we are requiring it in this integration
        'company_code' => ['type' => 'string', 'length' => 25, 'required' => true],
        'currency_code' => ['type' => 'string', 'length' => 3],
        'customer_code' => ['type' => 'string', 'length' => 50, 'required' => true],
        'customer_usage_type' => ['type' => 'string', 'length' => 25],
        'destination_address' => ['type' => 'object', 'class' => '\AvaTax\Address', 'required' => true],
        'detail_level' => [
            'type' => 'string',
            'options' => ['Document', 'Diagnostic', 'Line', 'Summary', 'Tax']
        ],
        'discount' => ['type' => 'float'],
        'doc_code' => ['type' => 'string', 'length' => 50],
        'doc_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/', 'required' => true],
        'doc_type' => [
            'type' => 'string',
            'options' =>
                ['SalesOrder', 'SalesInvoice', 'PurchaseOrder', 'PurchaseInvoice', 'ReturnOrder', 'ReturnInvoice'],
            'required' => true,
        ],
        'exchange_rate' => ['type' => 'float'],
        'exchange_rate_eff_date' => [
            'type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'exemption_no' => ['type' => 'string', 'length' => 25],
        'lines' => [
            'type' => 'array',
            'length' => 15000,
            'subtype' => ['*' => ['type' => 'object', 'class' => '\AvaTax\Line']],
            'required' => true,
        ],
        'location_code' => ['type' => 'string', 'length' => 50],
        'origin_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'payment_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'purchase_order_number' => ['type' => 'string', 'length' => 50],
        'reference_code' => ['type' => 'string', 'length' => 50],
        'salesperson_code' => ['type' => 'string', 'length' => 25],
        'tax_override' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
    ];

    /**
     * Format for the AvaTax dates
     */
    const AVATAX_DATE_FORMAT = 'Y-m-d';

    /**
     * Prefix for the DocCode field
     */
    const AVATAX_DOC_CODE_PREFIX = 'quote-';

    /**
     * Magento and AvaTax calculate tax rate differently (8.25 and 0.0825, respectively), so this multiplier is used to
     * convert AvaTax rate to Magento's rate
     */
    const RATE_MULTIPLIER = 100;

    /**
     * Class constructor
     *
     * @param Address $address
     * @param Config $config
     * @param Validation $validation
     * @param TaxServiceSoapFactory $taxServiceSoapFactory
     * @param GetTaxRequestFactory $getTaxRequestFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     * @param Line $interactionLine
     * @param TaxCalculation $taxCalculation
     * @param QuoteDetailsItemExtensionFactory $extensionFactory
     */
    public function __construct(
        Address $address,
        Config $config,
        Validation $validation,
        TaxServiceSoapFactory $taxServiceSoapFactory,
        GetTaxRequestFactory $getTaxRequestFactory,
        GroupRepositoryInterface $groupRepository,
        TaxClassRepositoryInterface $taxClassRepository,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        Line $interactionLine,
        TaxCalculation $taxCalculation,
        QuoteDetailsItemExtensionFactory $extensionFactory
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->validation = $validation;
        $this->taxServiceSoapFactory = $taxServiceSoapFactory;
        $this->getTaxRequestFactory = $getTaxRequestFactory;
        $this->groupRepository = $groupRepository;
        $this->taxClassRepository = $taxClassRepository;
        $this->priceCurrency = $priceCurrency;
        $this->localeDate = $localeDate;
        $this->interactionLine = $interactionLine;
        $this->taxCalculation = $taxCalculation;
        $this->extensionFactory = $extensionFactory;
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
     * @param $quote
     * @return string
     */
    protected function getCustomerCodeForQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        switch ($this->config->getCustomerCodeFormat($quote->getStoreId())) {
            case Config::CUSTOMER_FORMAT_OPTION_EMAIL:
                $email = $quote->getCustomerEmail();
                return $email ?: Config::CUSTOMER_MISSING_EMAIL;
                break;
            case Config::CUSTOMER_FORMAT_OPTION_NAME_ID:
                $customer = $quote->getCustomer();
                if ($customer->getId()) {
                    $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $id = $customer->getId();
                } else {
                    $name = $quote->getShippingAddress()->getFirstname() . ' ' . $quote->getShippingAddress()->getLastname();
                    if (!trim($name)) {
                        $name = Config::CUSTOMER_MISSING_NAME;
                    }
                    $id = Config::CUSTOMER_GUEST_ID;
                }
                return sprintf(Config::CUSTOMER_FORMAT_NAME_ID, $name, $id);
                break;
            case Config::CUSTOMER_FORMAT_OPTION_ID:
            default:
                return $quote->getCustomerId() ?: strtolower(Config::CUSTOMER_GUEST_ID) . '-' . $quote->getId();
                break;
        }
    }

    /**
     * Return the exchange rate between base currency and destination currency code
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $scope
     * @param string $baseCurrencyCode
     * @param string $convertCurrencyCode
     * @return float
     */
    protected function getExchangeRate($scope, $baseCurrencyCode, $convertCurrencyCode)
    {
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->priceCurrency->getCurrency($scope, $baseCurrencyCode);

        $rate = $currency->getRate($convertCurrencyCode);
        return $rate;
    }

    /**
     * Convert an order into data to be used in some kind of tax request
     * TODO: Find out what happens if Business Identification Number is passed and we do not want to consider VAT.  Probably add config field to allow user to not consider VAT.  Hide the Business Identification Number field using depends node.
     * TODO: Map config field of Business Identification Number to one in our module config.
     * TODO: Use Tax Class to get customer usage code, once this functionality is implemented
     * TODO: Make sure discount lines up proportionately with how Magento does it and if not, figure out if there is another way to do it.
     * TODO: Account for non item based lines according to documentation and M1 module
     * TODO: Implement Payment Date on Invoice Conversion and on Credit Memo Conversion.  M1 version is doing this.
     * TODO: Determine how to get parent increment id if one is set on order and set it on reference code
     * TODO: Determine what circumstance tax override will need to be set and set in order in those cases
     * TODO: For salesperson_code do at least a config field's value and possible make it configurable to allow for multiple formats including: just the code, just the admin user's role, just the admin user's First Name & Last Name, just the admin users username, just the admin user's email address, or some combinations of the options
     * TODO: Set up a config field for location_code to be passed along
     * TODO: Take calculate tax on shipping vs. billing address into account, this is a configuration field in default Magento, fall back if the selected one is missing
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function convertOrderToData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $customerGroupId = $order->getCustomerGroupId();
        if (!is_null($customerGroupId)) {
            $taxClassId = $this->groupRepository->getById($customerGroupId)->getTaxClassId();
            $taxClass = $this->taxClassRepository->get($taxClassId);
        }

        $lines = [];
        foreach ($order->getItems() as $item) {
            $line = $this->interactionLine->getLine($item);
            if ($line) {
                $lines[] = $line;
            }
        }

        // Shipping Address not documented in the interface for some reason
        // they do have a constant for it but not a method in the interface

        try {
            $address = $this->address->getAddress($order->getShippingAddress());
        } catch (LocalizedException $e) {
            return null;
        }

        $store = $order->getStore();
        $currentDate = $this->getFormattedDate($store);
        $docDate = $this->getFormattedDate($store, $order->getCreatedAt());

        return [
            'store_id' => $store->getId(),
            'commit' => $this->shouldCommit($order),
            'currency_code' => $order->getOrderCurrencyCode(), // TODO: Make sure these all map correctly
            'customer_code' => $this->getCustomerCode(
                $order->getCustomerFirstname(),
                $order->getCustomerEmail(),
                $order->getCustomerId()
            ),
//            'customer_usage_type' => null,//$taxClass->,
            'destination_address' => $address,
            'discount' => $order->getDiscountAmount(),
            'doc_code' => $order->getIncrementId(),
            'doc_date' => $docDate,
            'doc_type' => DocumentType::$PurchaseInvoice,
            'exchange_rate' => $this->getExchangeRate($store, $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()),
            'exchange_rate_eff_date' => $currentDate,
            'lines' => $lines,
//            'payment_date' => null,
            'purchase_order_number' => $order->getIncrementId(),
//            'reference_code' => null, // Most likely only set on credit memos or order edits
//            'salesperson_code' => null,
//            'tax_override' => null,
        ];
    }

    protected function convertTaxQuoteDetailsToData(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $taxClassId = $quote->getCustomerTaxClassId();
        if (!is_null($taxClassId)) {
            $taxClass = $this->taxClassRepository->get($taxClassId);
        }

        $lines = [];

        $items = $taxQuoteDetails->getItems();
        $keyedItems = $this->taxCalculation->getKeyedItems($items);
        $childrenItems = $this->taxCalculation->getChildrenItems($items);

        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($keyedItems as $item) {
            /**
             * If a quote has children and they are calculated (e.g., Bundled products with dynamic pricing)
             * @see \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItems
             * then we only need to pass child items to AvaTax. Due to the logic in
             * @see \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTaxDetails
             * the parent tax gets calculated based on children items
             */
            //
            if (isset($childrenItems[$item->getCode()])) {
                /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $childItem */
                foreach ($childrenItems[$item->getCode()] as $childItem) {
                    $line = $this->interactionLine->getLine($childItem);
                    if ($line) {
                        $lines[] = $line;
                    }
                }
            } else {
                $line = $this->interactionLine->getLine($item);
                if ($line) {
                    $lines[] = $line;
                }
            }
        }

        // Shipping Address not documented in the interface for some reason
        // they do have a constant for it but not a method in the interface
        try {
            $shippingAddress = $shippingAssignment->getShipping()->getAddress();
            $address = $this->address->getAddress($shippingAddress);
        } catch (LocalizedException $e) {
            // TODO: Log this exception
            return null;
        }

        $store = $quote->getStore();
        $currentDate = $this->getFormattedDate($store);

        // Quote created/updated date is not relevant, so just pass the current date
        $docDate = $currentDate;

        return [
            'store_id' => $store->getId(),
            'commit' => false,
            'currency_code' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'customer_code' => $this->getCustomerCodeForQuote($quote),
//            'customer_usage_type' => null,//$taxClass->,
            'destination_address' => $address,
            'doc_code' => self::AVATAX_DOC_CODE_PREFIX . $quote->getId(),
            'doc_date' => $docDate,
            'doc_type' => DocumentType::$PurchaseOrder,
            'exchange_rate' => $this->getExchangeRate($store, $quote->getCurrency()->getBaseCurrencyCode(), $quote->getCurrency()->getQuoteCurrencyCode()),
            'exchange_rate_eff_date' => $currentDate,
            'lines' => $lines,
//            'payment_date' => null,
            'purchase_order_number' => $quote->getReservedOrderId(),
//            'reference_code' => null, // Most likely only set on credit memos or order edits
//            'salesperson_code' => null,
//            'tax_override' => null,
        ];
    }

    protected function convertCreditMemoToData(\Magento\Sales\Api\Data\CreditmemoInterface $creditMemo)
    {
    }

    /**
     * Creates and returns a populated getTaxRequest for a quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return null|GetTaxRequest
     * @throws LocalizedException
     */
    public function getGetTaxRequestForQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $data = $this->convertTaxQuoteDetailsToData($taxQuoteDetails, $shippingAssignment, $quote);

        if (is_null($data)) {
            return null;
        }

        $store = $quote->getStore();
        $data = array_merge(
            $this->retrieveGetTaxRequestFields($store),
            $data
        );

        $data = $this->validation->validateData($data, $this->validDataFields);

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->getTaxRequestFactory->create();

        $this->populateGetTaxRequest($data, $getTaxRequest);

        return $getTaxRequest;
    }

    /**
     * Creates and returns a populated getTaxRequest for a quote
     * Note: detail_level != Line, Tax, or Diagnostic will result in an error if getTaxLines is called on response.
     * TODO: Switch detail_level to Tax once out of development.  Diagnostic is for development mode only and Line is the only other mode that provides enough info.  Check to see if M1 is using Line or Tax and then decide.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @return null|GetTaxRequest
     * @throws LocalizedException
     */
    public function getGetTaxRequestForInvoice(
        \Magento\Sales\Api\Data\InvoiceInterface $invoice
    ) {
        $data = $this->convertInvoiceToData($invoice);

        if (is_null($data)) {
            return null;
        }

        $storeId = $invoice->getStoreId();
        $data = array_merge(
            $this->retrieveGetTaxRequestFields($storeId),
            $data
        );

        $data = $this->validation->validateData($data, $this->validDataFields);

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->getTaxRequestFactory->create();

        $this->populateGetTaxRequest($data, $getTaxRequest);

        return $getTaxRequest;
    }

    protected function convertInvoiceToData(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        return false;
    }

    /**
     * Get details for GetTaxRequest
     *
     * Note: detail_level != Line, Tax, or Diagnostic will result in an error if getTaxLines is called on response.
     * TODO: Switch detail_level to Tax once out of development.  Diagnostic is for development mode only and Line is the only other mode that provides enough info.  Check to see if M1 is using Line or Tax and then decide.
     *
     * @param $store
     * @return array
     * @throws LocalizedException
     */
    protected function retrieveGetTaxRequestFields($store)
    {
        if ($this->config->getLiveMode($store) == Config::API_PROFILE_NAME_PROD) {
            $companyCode = $this->config->getCompanyCode($store);
        } else {
            $companyCode = $this->config->getDevelopmentCompanyCode($store);
        }
        return [
            'business_identification_no' => $this->config->getBusinessIdentificationNumber($store),
            'company_code' => $companyCode,
            'detail_level' => DetailLevel::$Diagnostic,
            // TODO: Create a graceful way of handling this address being missing and notifying admin user that they need to set up their shipping origin address
            'origin_address' => $this->address->getAddress($this->config->getOriginAddress($store)),
        ];
    }

    /**
     * Map data array to methods in GetTaxRequest object
     *
     * @param array $data
     * @param GetTaxRequest $getTaxRequest
     * @return GetTaxRequest
     */
    public function populateGetTaxRequest(array $data, GetTaxRequest $getTaxRequest)
    {
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

    /**
     * Return date in the current scope's timezone, formatted in AvaTax format
     *
     * @param $scope
     * @param null $time
     * @return string
     */
    protected function getFormattedDate($scope, $time = null)
    {
        $time = $time ?: 'now';
        $timezone = $this->localeDate->getConfigTimezone(null, $scope);
        $date = new \DateTime($time, new \DateTimeZone($this->localeDate->getDefaultTimezone()));
        $date->setTimezone(new \DateTimeZone($timezone));
        return $date->format(self::AVATAX_DATE_FORMAT);
    }
}
