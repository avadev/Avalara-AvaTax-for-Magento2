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
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
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
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter = null;

    /**
     * @var Line
     */
    protected $interactionLine = null;

    /**
     * @var TaxServiceSoap[]
     */
    protected $taxServiceSoap = [];

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

    public function __construct(
        Address $address,
        Config $config,
        Validation $validation,
        TaxServiceSoapFactory $taxServiceSoapFactory,
        GetTaxRequestFactory $getTaxRequestFactory,
        GroupRepositoryInterface $groupRepository,
        TaxClassRepositoryInterface $taxClassRepository,
        PriceCurrencyInterface $priceCurrency,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        DateTimeFormatter $dateTimeFormatter,
        Line $interactionLine
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->validation = $validation;
        $this->taxServiceSoapFactory = $taxServiceSoapFactory;
        $this->getTaxRequestFactory = $getTaxRequestFactory;
        $this->groupRepository = $groupRepository;
        $this->taxClassRepository = $taxClassRepository;
        $this->priceCurrency = $priceCurrency;
        $this->taxDetailsItemDataObjectFactory = $taxDetailsItemDataObjectFactory;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->interactionLine = $interactionLine;
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
     * TODO: Make sure this function works even if any of these values are empty
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
            if (!is_null($line)) {
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

        return [
            'store_id' => $order->getStoreId(),
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
            'doc_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter($order->getCreatedAt()), // TODO: Account for GMT Conversion
            'doc_type' => DocumentType::$PurchaseInvoice,
            'exchange_rate' => $this->getExchangeRate($order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()),
            'exchange_rate_eff_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter(time()),
            'lines' => $lines,
//            'payment_date' => null,
            'purchase_order_number' => $order->getIncrementId(),
//            'reference_code' => null, // Most likely only set on credit memos or order edits
//            'salesperson_code' => null,
//            'tax_override' => null,
        ];
    }
    
    protected function convertQuoteToData(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $taxClassId = $quote->getCustomerTaxClassId();
        if (!is_null($taxClassId)) {
            $taxClass = $this->taxClassRepository->get($taxClassId);
        }

        $lines = [];
        $items = $quote->getItems();

        if (is_null($items)) {
            if (method_exists($quote, 'getItemsCollection')) {
                $items = $quote->getItemsCollection()->count() > 0 ? $quote->getItemsCollection() : null;
            }
            if (is_null($items)) {
                return null;
            }
        }
        foreach ($items as $item) {
            $line = $this->interactionLine->getLine($item);
            if (!is_null($line)) {
                $lines[] = $line;
            }
        }

        // Shipping Address not documented in the interface for some reason
        // they do have a constant for it but not a method in the interface

        try {
            $address = $this->address->getAddress($quote->getShippingAddress());
        } catch (LocalizedException $e) {
            // TODO: Log this exception
            return null;
        }

        return [
            'store_id' => $quote->getStoreId(),
            'commit' => false,
            'currency_code' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'customer_code' => $this->getCustomerCode(
                $quote->getShippingAddress()->getFirstname(), // TODO: Determine if these three values will always be available and if they won't use ones that will
                $quote->getCustomer()->getEmail(),
                $quote->getCustomer()->getId()
            ),
//            'customer_usage_type' => null,//$taxClass->,
            'destination_address' => $address,
//            'discount' => $quote->getDiscountAmount(), // TODO: Determine if discounts are available on quotes
            'doc_code' => $quote->getReservedOrderId(),
            'doc_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter($quote->getCreatedAt()), // TODO: Account for GMT Conversion
            'doc_type' => DocumentType::$PurchaseOrder,
            'exchange_rate' => $this->getExchangeRate($quote->getCurrency()->getBaseCurrencyCode(), $quote->getCurrency()->getQuoteCurrencyCode()),
            'exchange_rate_eff_date' => $this->dateTimeFormatter->setFormat('Y-m-d')->filter(time()),
            'lines' => $lines,
//            'payment_date' => null,
            'purchase_order_number' => $quote->getReservedOrderId(),
//            'reference_code' => null, // Most likely only set on credit memos or order edits
//            'salesperson_code' => null,
//            'tax_override' => null,
        ];
    }

    protected function convertInvoiceToData(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {

    }

    protected function convertCreditMemoToData(\Magento\Sales\Api\Data\CreditmemoInterface $creditMemo)
    {

    }

    /**
     * Creates and returns a populated getTaxRequest
     * Note: detail_level != Line, Tax, or Diagnostic will result in an error if getTaxLines is called on response.
     * TODO: Switch detail_level to Tax once out of development.  Diagnostic is for development mode only and Line is the only other mode that provides enough info.  Check to see if M1 is using Line or Tax and then decide.
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data \Magento\Sales\Api\Data\OrderInterface|\Magento\Quote\Api\Data\CartInterface|\Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface|array
     * @return null|GetTaxRequest
     */
    public function getGetTaxRequest($data)
    {
        switch (true) {
            case ($data instanceof \Magento\Sales\Api\Data\OrderInterface):
                $data = $this->convertOrderToData($data);
                break;
            case ($data instanceof \Magento\Quote\Api\Data\CartInterface):
                $data = $this->convertQuoteToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceInterface):
                $data = $this->convertInvoiceToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoInterface):
                $data = $this->convertCreditMemoToData($data);
                break;
            case (!is_array($data)):
                return false;
                break;
        }

        if (is_null($data)) {
            return null;
        }

        $storeId = isset($data['store_id']) ? $data['store_id'] : null;
        if ($this->config->getLiveMode() == Config::API_PROFILE_NAME_PROD) {
            $companyCode = $this->config->getCompanyCode($storeId);
        } else {
            $companyCode = $this->config->getDevelopmentCompanyCode($storeId);
        }
        $data = array_merge(
            [
                'business_identification_no' => $this->config->getBusinessIdentificationNumber(),
                'company_code' => $companyCode,
                'detail_level' => DetailLevel::$Diagnostic,
                'origin_address' => $this->address->getAddress($this->config->getOriginAddress($storeId)), // TODO: Create a graceful way of handling this address being missing and notifying admin user that they need to set up their shipping origin address
            ],
            $data
        );

        $data = $this->validation->validateData($data, $this->validDataFields);

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

    /**
     * Convert a quote/order/invoice/credit memo item to a tax details item object
     *
     * @param $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    public function getTaxDetailsItem($item, \AvaTax\GetTaxResult $getTaxResult, $useBaseCurrency)
    {
        switch (true) {
            case ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface):
                // TODO: Create this method
                return $this->convertOrderItemToTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Quote\Api\Data\CartItemInterface):
                return $this->convertQuoteItemToTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                // TODO: Create this method
                return $this->convertInvoiceItemToTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
                break;
            case ($item instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                // TODO: Create this method
                return $this->convertCreditmemoItemToTaxDetailsItem($item, $getTaxResult, $useBaseCurrency);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Convert quote item to tax details item
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param \AvaTax\GetTaxResult $getTaxResult
     * @param $useBaseCurrency
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    public function convertQuoteItemToTaxDetailsItem(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        \AvaTax\GetTaxResult $getTaxResult,
        $useBaseCurrency
    ) {
        /* @var $taxLine \AvaTax\TaxLine  */
        $taxLine = $getTaxResult->getTaxLine($item->getId());

        // Items that are children of other items won't have lines in the response
        if (!$taxLine instanceof \AvaTax\TaxLine) {
            return false;
        }

        $rate = (float)$taxLine->getRate(); // TODO: Make sure we don't need to convert
        $tax = (float)$taxLine->getTax();

        $discountTaxCompensationAmount  = 0; // TODO: Add support for this
        $appliedTaxes = [];


        $quantity = $item->getQty(); // TODO: Add support for getting QTY from parent products See \Magento\Tax\Model\TaxCalculation::getTotalQuantity

        // See \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem
        if ($useBaseCurrency) {
            if (!$item->getBaseTaxCalculationPrice()) {
                $item->setBaseTaxCalculationPrice($item->getBaseCalculationPriceOriginal());
            }
            $price = $item->getBaseTaxCalculationPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        } else {
            if (!$item->getTaxCalculationPrice()) {
                $item->setTaxCalculationPrice($item->getCalculationPriceOriginal());
            }
            $price = $item->getTaxCalculationPrice(); // TODO: Run through $this->calculationTool->round($item->getUnitPrice());
        }
        // TODO: Determine if we need to only round if certain admin settings are configured
        $price = $this->priceCurrency->round($price);

        $rowTotal = $price * $quantity;

        if ($useBaseCurrency) {
            $rowTax = $tax;
        } else {
            // TODO: Pass current store view to this method
            $rowTax = $this->priceCurrency->convert($tax);
        }
        // TODO: Determine if we need to only round if certain admin settings are configured
        $rowTax = $this->priceCurrency->round($rowTax);

        $priceInclTax = $price + $rowTax;
        $rowTotalInclTax = $rowTotal + $rowTax;


        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getTaxCalculationItemId()) // this may need to change to something like sequence-***
            ->setType('product') // Correct data?
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes)
            ;
    }
}