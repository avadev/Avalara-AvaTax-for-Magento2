<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\DetailLevel;
use AvaTax\DocumentType;
use AvaTax\GetTaxRequest;
use AvaTax\GetTaxRequestFactory;
use AvaTax\TaxOverrideFactory;
use AvaTax\TaxServiceSoap;
use AvaTax\TaxServiceSoapFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Helper\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Tax
 */
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
     * @var \ClassyLlama\AvaTax\Helper\TaxClass
     */
    protected $taxClassHelper;

    /**
     * @var MetaData\MetaDataObject
     */
    protected $metaDataObject = null;

    /**
     * @var TaxServiceSoapFactory
     */
    protected $taxServiceSoapFactory = [];

    /**
     * @var GetTaxRequestFactory
     */
    protected $getTaxRequestFactory = null;

    /**
     * @var TaxOverrideFactory
     */
    protected $taxOverrideFactory = null;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository = null;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository = null;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository = null;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository = null;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository = null;

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
    protected $simpleTypes = ['boolean', 'integer', 'string', 'double'];

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.  If any fields are added or removed, the same should be done in getGetTaxRequest.
     *
     * @var array
     */
    public static $validFields = [
        'StoreId' => ['type' => 'integer'],
        'BusinessIdentificationNo' => ['type' => 'string', 'length' => 25],
        'Commit' => ['type' => 'boolean'],
        // Company Code is not required by the the API, but we are requiring it in this integration
        'CompanyCode' => ['type' => 'string', 'length' => 25, 'required' => true],
        'CurrencyCode' => ['type' => 'string', 'length' => 3],
        'CustomerCode' => ['type' => 'string', 'length' => 50, 'required' => true],
        'CustomerUsageType' => ['type' => 'string', 'length' => 25],
        'DestinationAddress' => ['type' => 'object', 'class' => '\AvaTax\Address', 'required' => true],
        'DetailLevel' => [
            'type' => 'string',
            'options' => ['Document', 'Diagnostic', 'Line', 'Summary', 'Tax']
        ],
        'Discount' => ['type' => 'double'],
        'DocCode' => ['type' => 'string', 'length' => 50],
        'DocDate' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/', 'required' => true],
        'DocType' => [
            'type' => 'string',
            'options' =>
                ['SalesOrder', 'SalesInvoice', 'PurchaseOrder', 'PurchaseInvoice', 'ReturnOrder', 'ReturnInvoice'],
            'required' => true,
        ],
        'ExchangeRate' => ['type' => 'double'],
        'ExchangeRateEffDate' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'ExemptionNo' => ['type' => 'string', 'length' => 25],
        'Lines' => [
            'type' => 'array',
            'length' => 15000,
            'subtype' => ['*' => ['type' => 'object', 'class' => '\AvaTax\Line']],
            'required' => true,
        ],
        'LocationCode' => ['type' => 'string', 'length' => 50],
        'OriginAddress' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'PaymentDate' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'PurchaseOrderNumber' => ['type' => 'string', 'length' => 50],
        'ReferenceCode' => ['type' => 'string', 'length' => 50],
        'SalespersonCode' => ['type' => 'string', 'length' => 25],
        'TaxOverride' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
    ];

    public static $validTaxOverrideFields = [
        'Reason' => ['type' => 'string', 'required' => true],
        'TaxOverrideType' => [
            'type' => 'string',
            'options' => ['None', 'TaxAmount', 'Exemption', 'TaxDate'],
            'required' => true,
        ],
        'TaxDate' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'TaxAmount' => ['type' => 'double'],
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
     * Reason for AvaTax override for creditmemos to specify tax date
     */
    const AVATAX_CREDITMEMO_OVERRIDE_REASON = 'Adjustment for return';

    /**
     * Magento and AvaTax calculate tax rate differently (8.25 and 0.0825, respectively), so this multiplier is used to
     * convert AvaTax rate to Magento's rate
     */
    const RATE_MULTIPLIER = 100;

    /**
     * Default currency exchange rate
     */
    const DEFAULT_EXCHANGE_RATE = 1;

    /**
     * Class constructor
     *
     * @param Address $address
     * @param Config $config
     * @param \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param TaxServiceSoapFactory $taxServiceSoapFactory
     * @param GetTaxRequestFactory $getTaxRequestFactory
     * @param TaxOverrideFactory $taxOverrideFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     * @param Line $interactionLine
     * @param TaxCalculation $taxCalculation
     * @param QuoteDetailsItemExtensionFactory $extensionFactory
     */
    public function __construct(
        Address $address,
        Config $config,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper,
        MetaDataObjectFactory $metaDataObjectFactory,
        TaxServiceSoapFactory $taxServiceSoapFactory,
        GetTaxRequestFactory $getTaxRequestFactory,
        TaxOverrideFactory $taxOverrideFactory,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        Line $interactionLine,
        TaxCalculation $taxCalculation,
        QuoteDetailsItemExtensionFactory $extensionFactory
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->taxClassHelper = $taxClassHelper;
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->taxServiceSoapFactory = $taxServiceSoapFactory;
        $this->getTaxRequestFactory = $getTaxRequestFactory;
        $this->taxOverrideFactory = $taxOverrideFactory;
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->storeRepository = $storeRepository;
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
     * Return customer code according to the admin configured format
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Sales\Api\Data\OrderInterface $data
     * @return string
     */
    protected function getCustomerCode($data)
    {
        switch ($this->config->getCustomerCodeFormat($data->getStoreId())) {
            case Config::CUSTOMER_FORMAT_OPTION_EMAIL:
                $email = $data->getCustomerEmail();
                return $email ?: Config::CUSTOMER_MISSING_EMAIL;
                break;
            case Config::CUSTOMER_FORMAT_OPTION_NAME_ID:
                $customer = $this->getCustomerById($data->getCustomerId());
                if ($customer && $customer->getId()) {
                    $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $id = $customer->getId();
                } else {
                    if (!$data->getIsVirtual()) {
                        $address = $data->getShippingAddress();
                    } else {
                        $address = $data->getBillingAddress();
                    }
                    $name = $address->getFirstname() . ' ' . $address->getLastname();
                    if (!trim($name)) {
                        $name = Config::CUSTOMER_MISSING_NAME;
                    }
                    $id = Config::CUSTOMER_GUEST_ID;
                }
                return sprintf(Config::CUSTOMER_FORMAT_NAME_ID, $name, $id);
                break;
            case Config::CUSTOMER_FORMAT_OPTION_ID:
            default:
                return $data->getCustomerId() ?: strtolower(Config::CUSTOMER_GUEST_ID) . '-' . $data->getId();
                break;
        }
    }

    /**
     * Get customer by ID
     *
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomerById($customerId)
    {
        if (!$customerId) {
            return null;
        }
        try {
            return $this->customerRepository->getById($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
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
        if (!$baseCurrencyCode || !$convertCurrencyCode) {
            return self::DEFAULT_EXCHANGE_RATE;
        }

        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->priceCurrency->getCurrency($scope, $baseCurrencyCode);

        $rate = $currency->getRate($convertCurrencyCode);
        return $rate;
    }

    /**
     * Convert an order into data to be used in some kind of tax request
     * TODO: Implement Payment Date on Invoice Conversion and on Credit Memo Conversion.  M1 version is doing this.
     * TODO: For salesperson_code do at least a config field's value and possible make it configurable to allow for multiple formats including: just the code, just the admin user's role, just the admin user's First Name & Last Name, just the admin users username, just the admin user's email address, or some combinations of the options
     *
     */

    /**
     * Convert Tax Quote Details into data to be converted to a GetTax Request
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array|null
     */
    protected function convertTaxQuoteDetailsToData(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
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
        //
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $address = $this->address->getAddress($shippingAddress);

        $store = $this->storeRepository->getById($quote->getStoreId());
        $currentDate = $this->getFormattedDate($store);

        // Quote created/updated date is not relevant, so just pass the current date
        $docDate = $currentDate;

        $customerUsageType = $quote->getCustomer()
            ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($quote->getCustomer())
            : null;
        return [
            'StoreId' => $store->getId(),
            'Commit' => false, // quotes should never be committed
            'CurrencyCode' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'CustomerCode' => $this->getCustomerCode($quote),
            'CustomerUsageType' => $customerUsageType,
            'DestinationAddress' => $address,
            'DocCode' => self::AVATAX_DOC_CODE_PREFIX . $quote->getId(),
            'DocDate' => $docDate,
            'DocType' => DocumentType::$PurchaseOrder,
            'ExchangeRate' => $this->getExchangeRate($store,
                $quote->getCurrency()->getBaseCurrencyCode(), $quote->getCurrency()->getQuoteCurrencyCode()),
            'ExchangeRateEffDate' => $currentDate,
            'Lines' => $lines,
            // This level of detail is needed in order to receive lines back in response
            'DetailLevel' => DetailLevel::$Line,
            'PurchaseOrderNumber' => $quote->getReservedOrderId(),
        ];
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
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $data = array_merge(
            $this->retrieveGetTaxRequestFields($store, $shippingAddress, $quote),
            $data
        );

        $data = $this->metaDataObject->validateData($data);

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->getTaxRequestFactory->create();

        $this->populateGetTaxRequest($data, $getTaxRequest);

        return $getTaxRequest;
    }

    /**
     * Creates and returns a populated getTaxRequest for a invoice
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $object
     * @return GetTaxRequest
     */
    public function getGetTaxRequestForSalesObject($object) {
        $order = $this->orderRepository->get($object->getOrderId());

        $lines = [];
        $items = $object->getItems();


        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($items as $item) {
            $line = $this->interactionLine->getLine($item);
            if ($line) {
                $lines[] = $line;
            }
        }

        $objectIsCreditMemo = ($object instanceof \Magento\Sales\Api\Data\CreditmemoInterface);

        $credit = $objectIsCreditMemo;
        $line = $this->interactionLine->getShippingLine($object, $credit);
        if ($line) {
            $lines[] = $line;
        }
        $line = $this->interactionLine->getGiftWrapItemsLine($object, $credit);
        if ($line) {
            $lines[] = $line;
        }
        $line = $this->interactionLine->getGiftWrapOrderLine($object, $credit);
        if ($line) {
            $lines[] = $line;
        }
        $line = $this->interactionLine->getGiftWrapCardLine($object, $credit);
        if ($line) {
            $lines[] = $line;
        }

        if ($objectIsCreditMemo) {
            $line = $this->interactionLine->getPositiveAdjustmentLine($object);
            if ($line) {
                $lines[] = $line;
            }
            $line = $this->interactionLine->getNegativeAdjustmentLine($object);
            if ($line) {
                $lines[] = $line;
            }
        }

        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $address */
        if (!$order->getIsVirtual()) {
            $address = $order->getShippingAddress();
        } else {
            $address = $order->getBillingAddress();
        }
        $avaTaxAddress = $this->address->getAddress($address);

        $store = $this->storeRepository->getById($object->getStoreId());
        $currentDate = $this->getFormattedDate($store);

        $currentDate = $this->getFormattedDate($store, $object->getCreatedAt());

        $taxOverride = null;
        if ($object instanceof \Magento\Sales\Api\Data\InvoiceInterface) {
            $docType = DocumentType::$SalesInvoice;
        } else {
            $docType = DocumentType::$ReturnInvoice;

            $invoice = $this->getInvoice($object->getInvoiceId());
            // If a Creditmemo was generated for an invoice, use the created_at value from the invoice
            if ($invoice) {
                $taxCalculationDate = $this->getFormattedDate($store, $invoice->getCreatedAt());
            } else {
                $taxCalculationDate = $this->getFormattedDate($store, $order->getCreatedAt());
            }

            // Set the tax date for calculation
            $taxOverride = $this->taxOverrideFactory->create();
            $taxOverride->setTaxDate($taxCalculationDate);
            $taxOverride->setTaxOverrideType(\AvaTax\TaxOverrideType::$TaxDate);
            $taxOverride->setTaxAmount(0.00);
            $taxOverride->setReason(self::AVATAX_CREDITMEMO_OVERRIDE_REASON);
        }

        $customer = $this->getCustomerById($order->getCustomerId());
        $customerUsageType = $customer ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($customer) : null;
        $data = [
            'StoreId' => $store->getId(),
            'Commit' => $this->config->getCommitSubmittedTransactions($store),
            'TaxOverride' => $taxOverride,
            'CurrencyCode' => $order->getOrderCurrencyCode(),
            'CustomerCode' => $this->getCustomerCode($order),
            'CustomerUsageType' => $customerUsageType,
            'DestinationAddress' => $avaTaxAddress,
            'DocCode' => $object->getIncrementId() . '123-' . rand(10000000,90000000000),
            'DocDate' => $currentDate,
            'DocType' => $docType,
            'ExchangeRate' => $this->getExchangeRate($store,
                $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()),
            'ExchangeRateEffDate' => $currentDate,
            'Lines' => $lines,
            // Only need document-level detail as we don't need lines in our response
            'DetailLevel' => DetailLevel::$Document,
            'PaymentDate' => $currentDate,
            'PurchaseOrderNumber' => $object->getIncrementId(),
        ];

        $data = array_merge(
            $this->retrieveGetTaxRequestFields($store, $address, $object),
            $data
        );

        $data = $this->metaDataObject->validateData($data);

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->getTaxRequestFactory->create();

        $this->populateGetTaxRequest($data, $getTaxRequest);

        return $getTaxRequest;
    }

    /**
     * Load invoice by id
     *
     * @param int|null $invoiceId
     * @return \Magento\Sales\Api\Data\InvoiceInterface|null
     */
    protected function getInvoice($invoiceId)
    {
        if ($invoiceId === null) {
            return null;
        }
        try {
            return $this->invoiceRepository->get($invoiceId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get details for GetTaxRequest
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param $address \Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Sales\Api\Data\OrderInterface $object
     * @return array
     * @throws LocalizedException
     */
    protected function retrieveGetTaxRequestFields(StoreInterface $store, $address, $object)
    {
        $customerId = $object->getCustomerId();
        $customer = $this->getCustomerById(($customerId));

        $storeId = $store->getId();
        if ($this->config->getLiveMode() == Config::API_PROFILE_NAME_PROD) {
            $companyCode = $this->config->getCompanyCode();
        } else {
            $companyCode = $this->config->getDevelopmentCompanyCode();
        }
        $businessIdentificationNumber = $this->getBusinessIdentificationNumber($store, $address, $customer);
        $locationCode = $this->config->getLocationCode($store);
        return [
            'BusinessIdentificationNo' => $businessIdentificationNumber,
            'CompanyCode' => $companyCode,
            'LocationCode' => $locationCode,
            'OriginAddress' => $this->address->getAddress($this->config->getOriginAddress($storeId)),
        ];
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param $store
     * @param $address
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return null
     */
    protected function getBusinessIdentificationNumber($store, $address, $customer)
    {
        if ($customer && $customer->getTaxvat()) {
            return $customer->getTaxvat();
        }
        if ($this->config->getUseBusinessIdentificationNumber($store)) {
            return $address->getVatId();
        }
        return null;
    }

    /**
     * Map data array to methods in GetTaxRequest object
     *
     * @param array $data
     * @param GetTaxRequest $getTaxRequest
     * @return GetTaxRequest
     */
    protected function populateGetTaxRequest(array $data, GetTaxRequest $getTaxRequest)
    {
        // Set any data elements that exist on the getTaxRequest
        foreach ($data as $key => $datum) {
            $methodName = 'set' . $key;
            if (method_exists($getTaxRequest, $methodName)) {
                $getTaxRequest->$methodName($datum);
            }
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
