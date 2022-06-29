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

use Avalara\DocumentType;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Customer;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class Tax
 */
class Tax
{

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \ClassyLlama\AvaTax\Helper\TaxClass
     */
    protected $taxClassHelper;

    /**
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var MetaData\MetaDataObject
     */
    protected $metaDataObject = null;

    /**
     * @var MetaData\MetaDataObject
     */
    protected $overrideMetaDataObject = null;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

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
    protected $interactionLine;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var RestConfig
     */
    protected $restConfig;

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.  If any fields are added or removed, the same should be done in getTaxRequest.
     *
     * @var array
     */
    public static $validFields = [
        'store_id' => ['type' => 'integer'],
        'business_identification_no' => ['type' => 'string', 'length' => 25],
        'commit' => ['type' => 'boolean'],
        // Company Code is not required by the the API, but we are requiring it in this integration
        'company_code' => ['type' => 'string', 'length' => 25, 'required' => true],
        'currency_code' => ['type' => 'string', 'length' => 3],
        'customer_code' => ['type' => 'string', 'length' => 50, 'required' => true],
        'entity_use_code' => ['type' => 'string', 'length' => 25],
        'discount' => ['type' => 'double'],
        'code' => ['type' => 'string', 'length' => 50],
        'date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'], // REST TransactionBuilder always uses current date
        'type' => [
            'type' => 'string',
            'options' =>
                ['SalesOrder', 'SalesInvoice', 'PurchaseOrder', 'PurchaseInvoice', 'ReturnOrder', 'ReturnInvoice',
                 "".DocumentType::C_ANY, "".DocumentType::C_SALESORDER, "".DocumentType::C_SALESINVOICE,
                 "".DocumentType::C_PURCHASEORDER, "".DocumentType::C_PURCHASEINVOICE,
                 "".DocumentType::C_RETURNORDER, "".DocumentType::C_RETURNINVOICE,
                 "".DocumentType::C_INVENTORYTRANSFERORDER, "".DocumentType::C_INVENTORYTRANSFERINVOICE,
                 "".DocumentType::C_REVERSECHARGEORDER, "".DocumentType::C_REVERSECHARGEINVOICE],
            'required' => true,
        ],
        'exchange_rate' => ['type' => 'double'],
        'exchange_rate_effective_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'lines' => [
            'type' => 'array',
            'length' => 15000,
            'subtype' => ['*' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject']],
            'required' => true,
        ],
        'addresses' => [
            'type' => 'array',
            'subtype' => ['*' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject']],
            'required' => true,
        ],
        'reporting_location_code' => ['type' => 'string', 'length' => 50],
        'purchase_order_no' => ['type' => 'string', 'length' => 50],
        'reference_code' => ['type' => 'string', 'length' => 50],
        'tax_override' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject'],
        'is_seller_importer_of_record' => ['type' => 'boolean'],
        'shipping_mode' => ['type' => 'string']
    ];

    public static $validTaxOverrideFields = [
        'reason' => ['type' => 'string', 'required' => true],
        'type' => [
            'type' => 'string',
            'options' => ['None', 'TaxAmount', 'Exemption', 'TaxDate',
                          "".DocumentType::C_ANY, "".DocumentType::C_SALESORDER, "".DocumentType::C_SALESINVOICE,
                          "".DocumentType::C_PURCHASEORDER, "".DocumentType::C_PURCHASEINVOICE,
                          "".DocumentType::C_RETURNORDER, "".DocumentType::C_RETURNINVOICE,
                          "".DocumentType::C_INVENTORYTRANSFERORDER, "".DocumentType::C_INVENTORYTRANSFERINVOICE,
                          "".DocumentType::C_REVERSECHARGEORDER, "".DocumentType::C_REVERSECHARGEINVOICE],
            'required' => true,
        ],
        'tax_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'tax_amount' => ['type' => 'double'],
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
     * @var \ClassyLlama\AvaTax\Helper\CustomsConfig
     */
    protected $customsConfig;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var AddressRepositoryInterface
     */
    protected $customerAddressRepository;

    /**
     * @param Address                                       $address
     * @param Config                                        $config
     * @param \ClassyLlama\AvaTax\Helper\TaxClass           $taxClassHelper
     * @param \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger
     * @param MetaDataObjectFactory                         $metaDataObjectFactory
     * @param DataObjectFactory                             $dataObjectFactory
     * @param CustomerRepositoryInterface                   $customerRepository
     * @param InvoiceRepositoryInterface                    $invoiceRepository
     * @param OrderRepositoryInterface                      $orderRepository
     * @param StoreRepositoryInterface                      $storeRepository
     * @param PriceCurrencyInterface                        $priceCurrency
     * @param TimezoneInterface                             $localeDate
     * @param Line                                          $interactionLine
     * @param TaxCalculation                                $taxCalculation
     * @param RestConfig                                    $restConfig
     * @param Customer                                      $customer
     * @param \ClassyLlama\AvaTax\Helper\CustomsConfig      $customsConfig
     * @param AddressRepositoryInterface                    $customerAddressRepository
     */
    public function __construct(
        Address $address,
        Config $config,
        \ClassyLlama\AvaTax\Helper\TaxClass $taxClassHelper,
        \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger,
        MetaDataObjectFactory $metaDataObjectFactory,
        DataObjectFactory $dataObjectFactory,
        CustomerRepositoryInterface $customerRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        Line $interactionLine,
        TaxCalculation $taxCalculation,
        RestConfig $restConfig,
        \ClassyLlama\AvaTax\Helper\CustomsConfig $customsConfig,
        Customer $customer,
        AddressRepositoryInterface $customerAddressRepository
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->taxClassHelper = $taxClassHelper;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->overrideMetaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validTaxOverrideFields]);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerRepository = $customerRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->storeRepository = $storeRepository;
        $this->priceCurrency = $priceCurrency;
        $this->localeDate = $localeDate;
        $this->interactionLine = $interactionLine;
        $this->taxCalculation = $taxCalculation;
        $this->restConfig = $restConfig;
        $this->customer = $customer;
        $this->customsConfig = $customsConfig;
        $this->customerAddressRepository = $customerAddressRepository;
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
     * Convert Tax Quote Details into data to be converted to a GetTax Request
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Framework\DataObject|null
     * @throws ValidationException
     * @throws LocalizedException
     */
    protected function convertTaxQuoteDetailsToRequest(
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

                    /**
                     * The Magento Core does not have the necessary details in the QuoteDetailsItem
                     * which are returned from the call to getItems() above in order to determine if
                     * the shipping type item has a discount or not as it is built differently than other
                     * product type items that include a discountAmount with the item. We can however
                     * determine this by examining the ShipmentAssignment that happens to store the
                     * details of the shipping calculation that occurred earlier in other collect totals.
                     */

                    // Check if we should adjust for a shipping discount amount
                    if ($this->isShippingDiscountAmountAdjustmentNeeded($shippingAssignment, $item, $line)) {

                        // Get the shipping discount amount from the address
                        $shippingDiscountAmount = $shippingAssignment->getShipping()->getAddress()->getShippingDiscountAmount();

                        // Recalculate the line amount with the shipping discount amount included
                        $amountAfterDiscount = ($item->getUnitPrice() * $item->getQuantity()) - $shippingDiscountAmount;

                        // Adjust the line amount
                        $line->setAmount($amountAfterDiscount);
                    }

                    $lines[] = $line;
                }
            }
        }

        // Shipping Address not documented in the interface for some reason
        // they do have a constant for it but not a method in the interface
        //
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $shippingAddress = $shippingAssignment->getShipping();
        $address = $this->address->getAddress($shippingAddress->getAddress());

        $store = $this->storeRepository->getById($quote->getStoreId());
        $currentDate = $this->getFormattedDate($store);

        $customerUsageType = $quote->getCustomer()
            ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($quote->getCustomer())
            : null;
        $data = [
            'store_id' => $store->getId(),
            'commit' => false, // quotes should never be committed
            'currency_code' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'customer_code' => $this->customer->getCustomerCodeByCustomerId(
                $quote->getCustomerId(),
                $quote->getId(),
                $quote->getStoreId()
            ),
            'entity_use_code' => $customerUsageType,
            'addresses' => [
                $this->restConfig->getAddrTypeTo() => $address,
            ],
            'code' => self::AVATAX_DOC_CODE_PREFIX . $quote->getId(),
            'type' => $this->restConfig->getDocTypeQuote(),
            'exchange_rate' => $this->getExchangeRate($store,
                $quote->getCurrency()->getBaseCurrencyCode(), $quote->getCurrency()->getQuoteCurrencyCode()),
            'exchange_rate_effective_date' => $currentDate,
            'lines' => $lines,
            'purchase_order_no' => $quote->getReservedOrderId(),
            'shipping_mode' => $this->customsConfig->getShippingTypeForMethod(
                $shippingAddress->getMethod(),
                $quote->getStoreId()
            )
        ];

        /** @var \Magento\Framework\DataObject $request */
        $request = $this->dataObjectFactory->create(['data' => $data]);

        return $request;
    }

    /**
     * Determine if item is a shipping type and a shipping discount amount exists on the address
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item
     * @param  \Magento\Framework\DataObject $line
     * @return bool
     */
    protected function isShippingDiscountAmountAdjustmentNeeded (
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item,
        $line
    ) {
        /**
         * When a cart discount is applied and is allowed to apply to the shipping amount
         * the QuoteDetailsItem does not include the discount amount for inclusion in tax caluclations
         *
         * The shipping discount amount does exist on the shipping address so we can check for
         * the existance of this specific type of QuoteDetailItem and the presence of a discount
         */

        if (
            is_object($line)
            && $item->getType() == CommonTaxCollector::ITEM_TYPE_SHIPPING
            && $item->getDiscountAmount() == null
            && ($item->getUnitPrice() * $item->getQuantity()) > 0
            && $line->getAmount() == ($item->getUnitPrice() * $item->getQuantity())
        ) {
            // The item is a shipping type detail item that does not already include a discount amount

            // Check for a shipping discount amount on the shipping address
            if (
                $shippingAssignment->getShipping()
                && $shippingAssignment->getShipping()->getAddress()
                && $shippingAssignment->getShipping()->getAddress()->getShippingDiscountAmount()
                && $shippingAssignment->getShipping()->getAddress()->getShippingDiscountAmount()
                    <= ($item->getUnitPrice() * $item->getQuantity())
            ) {
                // The shipping discount amount appears to exist and should be adjusted for
                return true;
            }
        }

        return false;
    }

    /**
     * Creates and returns a populated request object for a quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return null|\Magento\Framework\DataObject
     * @throws LocalizedException
     */
    public function getTaxRequestForQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $request = $this->convertTaxQuoteDetailsToRequest($taxQuoteDetails, $shippingAssignment, $quote);

        if (is_null($request)) {
            return null;
        }

        $store = $quote->getStore();
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $this->addGetTaxRequestFields($request, $store, $shippingAddress, $quote->getCustomerId());

        /**
         *  Adding importer of record override
         */
        if ($quote->getCustomerId() !== null) {
            $customer = $this->getCustomerById($quote->getCustomerId());

            if($customer !== null) {
                $this->setIsImporterOfRecord($customer, $request);
            }
        }

        try {
            $validatedData = $this->metaDataObject->validateData($request->getData());
            $request->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating data: ' . $e->getMessage(), [
                'data' => var_export($request->getData(), true)
            ]);
        }

        return $request;
    }

    /**
     * Creates and returns a populated tax request for a invoice
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $object
     * @return \Magento\Framework\DataObject
     * @throws ValidationException
     * @throws LocalizedException
     */
    public function getTaxRequestForSalesObject($object) {
        $order = $this->orderRepository->get($object->getOrderId());

        // Create an array of items for the order being processed
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            if (!$this->isProductCalculated($item)) {
                // Don't add configurable products to the array
                $orderItemsArray[$item->getProductID()] = $item;
            }
        }

        $lines = [];
        $items = $object->getItems();

        $this->taxClassHelper->populateCorrectTaxClasses($items, $object->getStoreId());
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($items as $item) {
            // Only add this item if it is in the order items array
            if (isset($orderItemsArray[$item->getProductId()])) {
                $line = $this->interactionLine->getLine($item);
                if ($line) {
                    $lines[] = $line;
                }
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

        $currentDate = $this->getFormattedDate($store, $object->getCreatedAt());

        $docType = null;
        $taxCalculationDate = null;
        if ($object instanceof \Magento\Sales\Api\Data\InvoiceInterface) {
            $docType = $this->restConfig->getDocTypeInvoice();

            if ($this->areTimesDifferentDays($order->getCreatedAt(), $object->getCreatedAt(), $object->getStoreId())) {
                $taxCalculationDate = $this->getFormattedDate($store, $order->getCreatedAt());
            }
        } else {
            $docType = $this->restConfig->getDocTypeCreditmemo();

            $invoice = $this->getInvoice($object->getInvoiceId());
            // If a Creditmemo was generated for an invoice, use the created_at value from the invoice
            if ($invoice) {
                $taxCalculationDate = $this->getFormattedDate($store, $invoice->getCreatedAt());
            } else {
                $taxCalculationDate = $this->getFormattedDate($store, $order->getCreatedAt());
            }
        }

        $taxOverride = null;
        if (!is_null($taxCalculationDate)) {
            // Set the tax date for calculation
            $taxOverrideData = [
                'tax_date' => $taxCalculationDate,
                'type' => $this->restConfig->getOverrideTypeDate(),
                'tax_amount' => 0.00,
                'reason' => self::AVATAX_CREDITMEMO_OVERRIDE_REASON,
            ];

            $taxOverride = $this->dataObjectFactory->create(['data' => $taxOverrideData]);

            $validatedData = $this->overrideMetaDataObject->validateData($taxOverride->getData());
            $taxOverride->setData($validatedData);
        }

        $customer = $this->getCustomerById($order->getCustomerId());
        $customerUsageType = $customer ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($customer) : null;

        $orderIncrementId = '';
        try {
            $order = $this->orderRepository->get($object->getOrderId());
            $orderIncrementId = $order->getIncrementId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Do nothing
        }

        $data = [
            'store_id' => $store->getId(),
            'commit' => $this->config->getCommitSubmittedTransactions($store),
            'date' => $currentDate,
            'tax_override' => $taxOverride,
            'currency_code' => $order->getOrderCurrencyCode(),
            'customer_code' => $this->customer->getCustomerCodeByCustomerId(
                $order->getCustomerId(),
                $order->getId(),
                $order->getStoreId()
            ),
            'entity_use_code' => $customerUsageType,
            'addresses' => [
                $this->restConfig->getAddrTypeTo() => $avaTaxAddress,
            ],
            'code' => $object->getIncrementId() . '123-' . rand(10000000,90000000000),
            'type' => $docType,
            'exchange_rate' => $this->getExchangeRate($store,
                $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()),
            'exchange_rate_effective_date' => $currentDate,
            'lines' => $lines,
            'purchase_order_no' => $object->getIncrementId(),
            'reference_code' => $orderIncrementId,
        ];

        $request = $this->dataObjectFactory->create(['data' => $data]);

        $this->addGetTaxRequestFields($request, $store, $address, $object->getOrder()->getCustomerId());

        if($customer !== null) {
            $this->setIsImporterOfRecord($customer, $request);
        }

        try {
            $validatedData = $this->metaDataObject->validateData($request->getData());
            $request->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating data: ' . $e->getMessage(), [
                'data' => var_export($request->getData(), true)
            ]);
        }

        return $request;
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
     * Get details for tax request
     *
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param                                        $address \Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface
     * @param int $customerId
     *
     * @throws LocalizedException
     */
    protected function addGetTaxRequestFields( $request, StoreInterface $store, $address, $customerId )
    {
        $customer = $this->getCustomerById( ($customerId) );

        $storeId = $store->getId();
        $companyCode = $this->config->getCompanyCode( $storeId );
        $locationCode = $this->config->getLocationCode( $storeId );
        $businessIdentificationNumber = $this->getBusinessIdentificationNumber( $store, $address, $customer );

        $additionalData = [
            'business_identification_no' => $businessIdentificationNumber,
            'company_code'               => $companyCode,
            'reporting_location_code'    => $locationCode,
        ];

        foreach ($additionalData as $key => $value)
        {
            $request->setData( $key, $value );
        }

        $originAddress = $this->address->getAddress( $this->config->getOriginAddress( $storeId ) );
        $addresses = ($request->hasAddresses()) ? $request->getAddresses() : [];
        $addresses[ $this->restConfig->getAddrTypeFrom() ] = $originAddress;
        $request->setAddresses( $addresses );
    }

    /**
     * @param $store
     * @param $address
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return null
     */
    protected function getBusinessIdentificationNumber($store, $address, $customer)
    {
        if (!$this->config->getUseBusinessIdentificationNumber($store)) {
            // 'Include VAT Tax' setting is disabled
            return null;
        }
        if ($address->getVatId()) {
            // Using the VAT ID has been assigned to the address
            return $address->getVatId();
        } else {
            // Trying to get vat id from customer address, if not exist in quote address
            try {
                $customerAddress = $this->customerAddressRepository->getById($address->getCustomerAddressId());
                if ($customerAddress->getVatId()) {
                    return $customerAddress->getVatId();
                }
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                // No actions needed
            }
        }
        if ($customer && $customer->getTaxvat()) {
            // Using the VAT ID assigned to the customer account
            return $customer->getTaxvat();
        }
        // No VAT ID available to use
        return null;
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

    /**
     * Check whether timestamps are from different days
     *
     * @param $timeA
     * @param $timeB
     * @param $storeId
     * @return bool
     */
    protected function areTimesDifferentDays($timeA, $timeB, $storeId)
    {
        if (!$timeA || !$timeB) {
            return true;
        }

        $timezone = $this->localeDate->getConfigTimezone(null, $storeId);
        $dateA = new \DateTime($timeA, new \DateTimeZone($this->localeDate->getDefaultTimezone()));
        $dateA->setTimezone(new \DateTimeZone($timezone));
        $dateB = new \DateTime($timeB, new \DateTimeZone($this->localeDate->getDefaultTimezone()));
        $dateB->setTimezone(new \DateTimeZone($timezone));

        return $dateA->format('Y-m-d') != $dateB->format('Y-m-d');
    }

    /**
     * Return whether product is calculated or not
     *
     * @param $item
     * @return bool
     */
    protected function isProductCalculated($item) {
        if (method_exists($item, 'isChildrenCalculated') && method_exists($item, 'getParentItem')) {
            if ($item->isChildrenCalculated() && !$item->getParentItem()) {
                return true;
            }
            if (!$item->isChildrenCalculated() && $item->getParentItem()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks to see if there is an override for is importer of record and applies this to the request.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \Magento\Framework\DataObject $request
     */
    protected function setIsImporterOfRecord($customer, $request)
    {
        $override = $customer->getCustomAttribute(CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE);
        $overrideValue = ($override !== null ? $override->getValue() : null);

        if($overrideValue !== null && $overrideValue !== CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_DEFAULT) {
            $request->setData('is_seller_importer_of_record',
                $overrideValue === CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_YES
            );
        }
    }
}
