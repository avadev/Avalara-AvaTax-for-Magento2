<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\CreditmemoRequestBuilderInterface;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Request\RequestFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Request\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use ClassyLlama\AvaTax\Helper\TaxClass as TaxClassHelper;
use ClassyLlama\AvaTax\Framework\Interaction\Request\LineBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddress;
use Magento\Store\Api\Data\StoreInterface as Store;
use Avalara\DocumentType;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as Timezone;
use \DateTime;
use \DateTimeZone;
use ClassyLlama\AvaTax\Helper\Rest\Config as HelperRestConfig;
use ClassyLlama\AvaTax\Framework\Interaction\Tax as FrameworkInteractionTax;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use ClassyLlama\AvaTax\Helper\TaxClass as AvaTaxTaxClassHelper;
use ClassyLlama\AvaTax\Helper\Customer;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelperConfig;
use ClassyLlama\AvaTax\Framework\Interaction\Request\AddressBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;
use Magento\Customer\Model\Data\Customer as CustomerModel;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use Magento\Framework\Api\AttributeInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * Class CreditmemoRequestBuilder
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 */
class CreditmemoRequestBuilder implements CreditmemoRequestBuilderInterface
{

    /**
     * Format for the AvaTax dates
     *
     * @var string
     */
    const AVATAX_DATE_FORMAT = 'Y-m-d';

    /**
     * @var float
     */
    const ZERO_TAX_AMOUNT = 0.00;

    /**
     * Commit transaction status
     *
     * @var bool
     */
    const COMMIT_TRANSACTION = false;

    /**
     * Default currency exchange rate
     *
     * @var int
     */
    const DEFAULT_EXCHANGE_RATE = 1;

    /**
     * @var string
     */
    const AVATAX_CREDITMEMO_ESTIMATION_OVERRIDE_REASON = 'Adjustment for the estimated return';

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TaxClassHelper
     */
    private $taxClassHelper;

    /**
     * @var LineBuilder
     */
    private $lineBuilder;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var HelperRestConfig
     */
    private $helperRestConfig;

    /**
     * @var MetaDataObject
     */
    private $metaDataObject;

    /**
     * @var MetaDataObject
     */
    private $validationMetadataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AvaTaxTaxClassHelper
     */
    private $avaTaxTaxClassHelper;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var AvaTaxHelperConfig
     */
    private $avataxHelperConfig;

    /**
     * @var AddressBuilder
     */
    private $addressBuilder;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * CreditmemoRequestBuilder constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param PriceCurrencyInterface $priceCurrency
     * @param AddressBuilder $addressBuilder
     * @param AvaTaxHelperConfig $avataxHelperConfig
     * @param Customer $customer
     * @param AvaTaxTaxClassHelper $avaTaxTaxClassHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param HelperRestConfig $helperRestConfig
     * @param DataObjectFactory $dataObjectFactory
     * @param Timezone $timezone
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param LineBuilder $lineBuilder
     * @param AvaTaxTaxClassHelper $taxClassHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        PriceCurrencyInterface $priceCurrency,
        AddressBuilder $addressBuilder,
        AvaTaxHelperConfig $avataxHelperConfig,
        Customer $customer,
        AvaTaxTaxClassHelper $avaTaxTaxClassHelper,
        CustomerRepositoryInterface $customerRepository,
        MetaDataObjectFactory $metaDataObjectFactory,
        HelperRestConfig $helperRestConfig,
        DataObjectFactory $dataObjectFactory,
        Timezone $timezone,
        InvoiceRepositoryInterface $invoiceRepository,
        StoreRepositoryInterface $storeRepository,
        LineBuilder $lineBuilder,
        TaxClassHelper $taxClassHelper,
        OrderRepositoryInterface $orderRepository,
        RequestFactory $requestFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->requestFactory = $requestFactory;
        $this->orderRepository = $orderRepository;
        $this->taxClassHelper = $taxClassHelper;
        $this->lineBuilder = $lineBuilder;
        $this->storeRepository = $storeRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->timezone = $timezone;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->helperRestConfig = $helperRestConfig;
        $this->customerRepository = $customerRepository;
        $this->avaTaxTaxClassHelper = $avaTaxTaxClassHelper;
        $this->customer = $customer;
        $this->avataxHelperConfig = $avataxHelperConfig;
        $this->addressBuilder = $addressBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->metaDataObject = $metaDataObjectFactory->create([
            'metaDataProperties' => FrameworkInteractionTax::$validTaxOverrideFields
        ]);
        $this->validationMetadataObject = $metaDataObjectFactory->create([
            'metaDataProperties' => FrameworkInteractionTax::$validFields
        ]);
    }

    /**
     * Creditmemo request builder
     *
     * @param CreditmemoInterface $creditmemo
     * @return RequestInterface
     * @throws \Throwable
     */
    public function build(CreditmemoInterface $creditmemo): RequestInterface
    {
        try {
            /** @var Order $order */
            $order = $this->orderRepository->get((int)$creditmemo->getOrderId());
        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }

        $orderItems = [];

        /** @var OrderItem $item */
        foreach ($order->getAllItems() as $item) {
            if (!$this->isProductCalculated($item)) {
                $orderItems[$item->getProductId()] = $item;
            }
        }

        $this->taxClassHelper->populateCorrectTaxClasses($creditmemo->getItems(), $creditmemo->getStoreId());

        try {

            /** @var array<int, DataObject> $lines */
            $lines = $this->lineBuilder->build($creditmemo, $orderItems, true);
            /** @var Store $store */
            $store = $this->storeRepository->getById((int)$creditmemo->getStoreId());
            /** @var OrderAddress $orderAddress */
            $orderAddress = (!$order->getIsVirtual()) ? $order->getShippingAddress() : $order->getBillingAddress();
            /** @var array $addresses */
            $addresses = $this->addressBuilder->build($order, (int)$store->getId());
            /** @var string $docType */
            $docType = $this->getDocTypeCreditmemoEstimation();
            /** @var string $taxCalculationDate */
            $taxCalculationDate = $this->getTaxCalculationDate(
                $this->getInvoice($creditmemo->getInvoiceId()),
                $order,
                $store
            );
            /** @var DataObject $taxOverride */
            $taxOverride = $this->getTaxOverrideData($taxCalculationDate);
            /** @var CustomerModel|null $customer */
            $customer = $this->getCustomerById((int)$order->getCustomerId());
            /** @var string|null $customerUsageType */
            $customerUsageType = $customer ? $this->avaTaxTaxClassHelper->getAvataxTaxCodeForCustomer($customer) : null;
            /** @var Request $request */
            $request = $this->createAndValidateRequest(
                $creditmemo,
                $store,
                $taxOverride,
                $order,
                $customerUsageType,
                $addresses,
                $docType,
                $lines,
                $orderAddress,
                $customer
            );

            return $request;

        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Returns whether a product is calculated or not
     *
     * @param OrderItem $item
     * @return bool
     */
    private function isProductCalculated(OrderItem $item): bool
    {
        if ($item->isChildrenCalculated() && !$item->getParentItem()) {
            return true;
        }

        if (!$item->isChildrenCalculated() && $item->getParentItem()) {
            return true;
        }

        return false;
    }

    /**
     * Represents an estimate of tax to be refunded if a refund or return is processed.
     *
     * This document type is used before a customer chooses to request a refund for a previous sale, and it
     * estimates the final amount of tax to be refunded when the refund is completed.
     *
     * For a return order, the `companyCode` of the transaction refers to the seller who is giving the refund
     * and the `customerVendorCode` refers to the buyer who is requesting the refund.
     *
     * This is a temporary document type and is not saved in tax history.
     *
     * @return string
     */
    private function getDocTypeCreditmemoEstimation(): string
    {
        return DocumentType::C_RETURNORDER;
    }

    /**
     * Load an Invoice by Id
     *
     * @param int|null $invoiceId
     * @return InvoiceInterface|null
     */
    private function getInvoice($invoiceId)
    {
        if (null !== $invoiceId) {
            try {
                return $this->invoiceRepository->get($invoiceId);
            } catch (\Throwable $exception) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get tax calculation date
     *
     * @param InvoiceInterface|null $invoice
     * @param Order $order
     * @param Store $store
     * @return string
     * @throws \Exception
     */
    private function getTaxCalculationDate($invoice, Order $order, Store $store): string
    {
        /**
         * - if creditmemo was generated for an Invoice, we will use the Invoice creation date.
         * - by default, we always will take Order creation date.
         */
        return null !== $invoice ? $this->getFormattedDate($store, (string)$invoice->getCreatedAt()):
            $this->getFormattedDate($store, (string)$order->getCreatedAt());
    }

    /**
     * Return date in the current scope's timezone, formatted in AvaTax format.
     * If will be passed not correct $createdAtTime input parameter, like for example "abs", the current time will be used.
     *
     * @param Store $store
     * @param string|null $createdAtTime
     * @return string
     * @throws \Exception
     */
    private function getFormattedDate(Store $store, $createdAtTime = ''): string
    {
        /** @var string $time */
        $time = !empty($createdAtTime) ? $createdAtTime : 'now';
        /** @var string $timezone */
        $timezone = $this->timezone->getConfigTimezone(null, $store);

        try {
            /** @var DateTime $date */
            $date = new DateTime($time, new DateTimeZone($this->timezone->getDefaultTimezone()));
            $date->setTimezone(new DateTimeZone($timezone));
            $date = $date->format(self::AVATAX_DATE_FORMAT);
        } catch (\Throwable $exception) {
            /** @var DateTime $date */
            $date = new DateTime('now', new DateTimeZone($this->timezone->getDefaultTimezone()));
            $date->setTimezone(new DateTimeZone($timezone));
            $date = $date->format(self::AVATAX_DATE_FORMAT);
        }

        return (string)$date;
    }

    /**
     * @param string $taxCalculationDate
     * @return DataObject
     * @throws ValidationException
     */
    private function getTaxOverrideData(string $taxCalculationDate): DataObject
    {
        /** @var DataObject $dataObject */
        $dataObject = $this->dataObjectFactory->create([
            'data' => [
                'tax_date' => $taxCalculationDate,
                'type' =>  $this->helperRestConfig->getOverrideTypeDate(),
                'tax_amount' => self::ZERO_TAX_AMOUNT,
                'reason' => self::AVATAX_CREDITMEMO_ESTIMATION_OVERRIDE_REASON
            ]
        ]);

        $validatedData = $this->metaDataObject->validateData($dataObject->getData());
        $dataObject->setData($validatedData);

        return $dataObject;
    }

    /**
     * Get a Customer by Id
     *
     * @param int|null $customerId
     * @return CustomerInterface|null
     */
    private function getCustomerById($customerId)
    {
        if (null !== $customerId) {
            try {
                return $this->customerRepository->getById($customerId);
            } catch (\Throwable $exception) {
                return null;
            }
        }
        return null;
    }

    /**
     * Return the exchange rate between the base currency and destination currency code
     *
     * @param Store $store
     * @param string $baseCurrencyCode
     * @param string $convertCurrencyCode
     * @return float
     */
    private function getExchangeRate(Store $store, string $baseCurrencyCode = '', string $convertCurrencyCode = ''): float
    {
        if (!empty($baseCurrencyCode) || !empty($convertCurrencyCode)) {
            /** @var Currency $currency */
            $currency = $this->priceCurrency->getCurrency($store, $baseCurrencyCode);
            return (float)$currency->getRate($convertCurrencyCode);
        }
        return (float)self::DEFAULT_EXCHANGE_RATE;
    }

    /**
     * Get Business Identification Number (VAT)
     *
     * @param Store $store
     * @param OrderAddress $address
     * @param CustomerModel|null $customer
     * @return string|null
     */
    private function getBusinessIdentificationNumber(Store $store, OrderAddress $address, $customer)
    {
        if (!$this->avataxHelperConfig->getUseBusinessIdentificationNumber($store)) {
            // 'Include VAT Tax' setting is disabled
            return null;
        }
        if ($address->getVatId()) {
            // Using the VAT ID has been assigned to the address
            return (string)$address->getVatId();
        }
        if (null !== $customer && $customer->getTaxvat()) {
            // Using the VAT ID assigned to the customer account
            return (string)$customer->getTaxvat();
        }
        // No VAT ID available to use
        return null;
    }

    /**
     * @param CustomerInterface $customer
     * @param Request $request
     * @return CreditmemoRequestBuilder
     */
    private function setIsImporterOfRecord(CustomerInterface $customer, Request $request): self
    {
        /** @var AttributeInterface|null $attribute */
        $attribute = $customer->getCustomAttribute(CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE);
        $attributeValue = (null !== $attribute) ? $attribute->getValue() : null;

        if (null !== $attributeValue && $attributeValue !== CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_DEFAULT) {
            $request->setData('is_seller_importer_of_record', $attributeValue === CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_YES);
        }

        return $this;
    }

    /**
     * Get random unique token
     *
     * @param int $limit
     * @return string
     * @throws \Exception
     */
    private function getToken(int $limit = 24): string
    {
        return (string)substr(base_convert(sha1((string)random_int(0, PHP_INT_MAX)), 16, 36), 0, $limit);
    }

    /**
     * Create and validate Request object
     *
     * @param CreditmemoInterface $creditmemo
     * @param Store $store
     * @param DataObject $taxOverride
     * @param Order $order
     * @param string|null $customerUsageType
     * @param array $addresses
     * @param string $docType
     * @param array $lines
     * @param OrderAddress $orderAddress
     * @param CustomerModel|null $customer
     * @return Request
     * @throws \Exception
     */
    private function createAndValidateRequest(
        CreditmemoInterface $creditmemo,
        Store $store,
        DataObject $taxOverride,
        Order $order,
        $customerUsageType,
        array $addresses,
        string $docType,
        array $lines,
        OrderAddress $orderAddress,
        $customer
    ): Request {

        /**
         * Request creation
         *
         * @var Request $request
         */
        $request = $this->requestFactory->create();
        $request->setData('store_id', $store->getId());
        $request->setData('commit', self::COMMIT_TRANSACTION);
        $request->setData('tax_override', $taxOverride);
        $request->setData('currency_code', $order->getOrderCurrencyCode());
        $request->setData('customer_code', $this->customer->getCustomerCodeByCustomerId(
            $order->getCustomerId(),
            $order->getId(),
            $order->getStore()
        ));
        $request->setData('entity_use_code', $customerUsageType);
        $request->setData('addresses', $addresses);
        $request->setData('code', $this->getToken());
        $request->setData('type', $docType);
        $request->setData(
            'exchange_rate',
            $this->getExchangeRate($store, $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode())
        );
        $request->setData('exchange_rate_effective_date', $this->getFormattedDate($store, $creditmemo->getCreatedAt()));
        $request->setData('lines', $lines);
        $request->setData('purchase_order_no', $creditmemo->getIncrementId());
        $request->setData('reference_code', $order->getIncrementId());
        $request->setData('business_identification_no', $this->getBusinessIdentificationNumber($store, $orderAddress, $customer));
        $request->setData('company_code', $this->avataxHelperConfig->getCompanyCode((int)$store->getId()));
        $request->setData('reporting_location_code', $this->avataxHelperConfig->getLocationCode((int)$store->getId()));

        if (null !== $customer) {
            $this->setIsImporterOfRecord($customer, $request);
        }

        /**
         * Request validation
         */
        try {
            /** @var array $validatedData */
            $validatedData = $this->validationMetadataObject->validateData($request->getData());
            $request->setData($validatedData);
        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error('Error validating data: ' . $exception->getMessage(), [
                'data' => var_export($request->getData(), true)
            ]);
        }

        return $request;
    }
}
