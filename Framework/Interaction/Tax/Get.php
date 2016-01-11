<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax;

use AvaTax\GetTaxRequest;
use AvaTax\GetTaxResult;
use AvaTax\LineFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Cacheable\TaxService;
use ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

class Get
{
    /**
     * @var TaxCalculation
     */
    protected $taxCalculation = null;

    /**
     * @var Address
     */
    protected $interactionAddress = null;

    /**
     * @var Tax
     */
    protected $interactionTax = null;

    /**
     * @var LineFactory
     */
    protected $lineFactory = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var Get\ResponseFactory
     */
    protected $getTaxResponseFactory;

    /**
     * @var null
     */
    protected $errorMessage = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var TaxService
     */
    protected $taxService = null;

    /**#@+
     * Keys for non-base and base tax details
     */
    const KEY_TAX_DETAILS = 'tax_details';

    const KEY_BASE_TAX_DETAILS = 'base_tax_details';
    /**#@-*/

    /**
     * @param TaxCalculation $taxCalculation
     * @param Address $interactionAddress
     * @param Tax $interactionTax
     * @param LineFactory $lineFactory
     * @param Config $config
     * @param Get\ResponseFactory $getTaxResponseFactory
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        TaxCalculation $taxCalculation,
        Address $interactionAddress,
        Tax $interactionTax,
        LineFactory $lineFactory,
        Config $config,
        // TODO: Figure out why a factory for the interface isn't working:
        //ClassyLlama\AvaTax\Api\Data\GetTaxResponseFactory $getTaxResponseFactory
        Get\ResponseFactory $getTaxResponseFactory,
        AvaTaxLogger $avaTaxLogger,
        TaxService $taxService
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->interactionAddress = $interactionAddress;
        $this->interactionTax = $interactionTax;
        $this->lineFactory = $lineFactory;
        $this->config = $config;
        $this->getTaxResponseFactory = $getTaxResponseFactory;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->taxService = $taxService;
    }

    /**
     * Process invoice or credit memo
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $object
     * @return \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface
     * @throws Get\Exception
     */
    public function processSalesObject($object)
    {
        $taxService = $this->taxService;
        try {
            /** @var $getTaxRequest GetTaxRequest */
            $getTaxRequest = $this->interactionTax->getGetTaxRequestForSalesObject($object);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->avaTaxLogger->warning($message);
            throw new Get\Exception($message, $e->getCode(), $e);
        }

        if (is_null($getTaxRequest)) {
            $message = '$getTaxRequest was empty so not running getTax request.';
            $this->avaTaxLogger->warning($message);
            throw new Get\Exception($message);
        }

        try {
            $getTaxResult = $taxService->getTax($getTaxRequest);
            if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
                $this->avaTaxLogger->info(
                    'response from external api getTax',
                    [ /* context */
                        'request' => var_export($getTaxRequest, true),
                        'result' => var_export($getTaxResult, true),
                    ]
                );

                // TODO: If debug logging is on, log the extra debug info
                //$this->extraDebug($getTaxRequest, $getTaxResult, $object);

                // Since credit memo tax amounts come back from AvaTax as negative numbers, get absolute value
                $avataxTaxAmount = abs($getTaxResult->getTotalTax());
                $unbalanced = ($avataxTaxAmount != $object->getBaseTaxAmount());

                /** @var $response \ClassyLlama\AvaTax\Api\Data\GetTaxResponseInterface */
                $response = $this->getTaxResponseFactory->create();
                $response->setIsUnbalanced($unbalanced)
                    ->setBaseAvataxTaxAmount($avataxTaxAmount);
                return $response;
            } else {
                $message = $this->getErrorMessageFromGetTaxResult($getTaxResult);

                $this->avaTaxLogger->warning(
                    $message,
                    [ /* context */
                        'request' => var_export($getTaxRequest, true),
                        'result' => var_export($getTaxResult, true),
                    ]
                );

                throw new Get\Exception($message);
            }
        } catch (\SoapFault $exception) {
            $message = "Exception: \n";
            if ($exception) {
                $message .= $exception->faultstring;
            }
            $message .= $taxService->__getLastRequest() . "\n";
            $message .= $taxService->__getLastResponse() . "\n";
            $this->avaTaxLogger->critical(
                "Exception: \n" . ($exception) ? $exception->faultstring: "",
                [ /* context */
                    'request' => var_export($taxService->__getLastRequest(), true),
                    'result' => var_export($taxService->__getLastResponse(), true),
                ]
            );

            throw new Get\Exception($message);
        }
    }

    /**
     * Convert quote/order/invoice/creditmemo to the AvaTax object and request tax from the Get Tax API
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $baseTaxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return bool|\Magento\Tax\Api\Data\TaxDetailsInterface[]
     */
    public function getTaxDetailsForQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $baseTaxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $taxService = $this->taxService;

        // Total quantity of an item can be determined by multiplying parent * child quantity, so it's necessary
        // to calculate total quantities on a list of all items
        $this->taxCalculation->calculateTotalQuantities($taxQuoteDetails->getItems());
        $this->taxCalculation->calculateTotalQuantities($baseTaxQuoteDetails->getItems());

        // Taxes need to be calculated on the base prices/amounts, not the current currency prices. As a result of this,
        // only the $baseTaxQuoteDetails will have taxes calculated for it. The taxes for the current currency will be
        // calculated by multiplying the base tax rates * currency conversion rate.
        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->interactionTax->getGetTaxRequestForQuote($quote, $baseTaxQuoteDetails, $shippingAssignment);

        if (is_null($getTaxRequest)) {
            // TODO: Possibly refactor all usages of setErrorMessage to throw exception instead so that this class can be stateless
            $this->setErrorMessage('$data was empty or address was not valid so not running getTax request.');
            $this->avaTaxLogger->warning('$data was empty or address was not valid so not running getTax request.');
            return false;
        }

        try {
            $getTaxResult = $taxService->getTax($getTaxRequest);
            if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
                $this->avaTaxLogger->info(
                    'response from external api getTax',
                    [ /* context */
                        'request' => var_export($getTaxRequest, true),
                        'result' => var_export($getTaxResult, true),
                    ]
                );

                $store = $quote->getStore();
                $taxDetails = $this->taxCalculation->calculateTaxDetails($taxQuoteDetails, $getTaxResult, false, $store);
                $baseTaxDetails = $this->taxCalculation->calculateTaxDetails($baseTaxQuoteDetails, $getTaxResult, true, $store);

                return [
                    self::KEY_TAX_DETAILS => $taxDetails,
                    self::KEY_BASE_TAX_DETAILS => $baseTaxDetails
                ];
            } else {
                // TODO: Generate better error message
                $this->setErrorMessage('Bad result code: ' . $getTaxResult->getResultCode());
                $this->avaTaxLogger->warning(
                    'Bad result code: ' . $getTaxResult->getResultCode(),
                    [ /* context */
                        'request' => var_export($getTaxRequest, true),
                        'result' => var_export($getTaxResult, true),
                    ]
                );
                return false;
            }
        } catch (\SoapFault $exception) {
            $message = "Exception: \n";
            if ($exception) {
                $message .= $exception->faultstring;
            }
            $message .= $taxService->__getLastRequest() . "\n";
            $message .= $taxService->__getLastResponse() . "\n";
            $this->setErrorMessage($message);
            $this->avaTaxLogger->critical(
                "Exception: \n" . ($exception) ? $exception->faultstring: "",
                [ /* context */
                    'request' => var_export($taxService->__getLastRequest(), true),
                    'result' => var_export($taxService->__getLastResponse(), true),
                ]
            );
        }
        return false;
    }

    /**
     * Set error message
     *
     * @param $message
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    /**
     * Return error message generated by calling the getTax method
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Get formatted error message from GetTaxResult
     *
     * @param GetTaxResult $getTaxResult
     * @return string
     */
    protected function getErrorMessageFromGetTaxResult(GetTaxResult $getTaxResult)
    {
        $message = '';

        $message .= __('Result code: ') . $getTaxResult->getResultCode() . PHP_EOL;

        /** @var \AvaTax\Message $avataxMessage */
        foreach ($getTaxResult->getMessages() as $avataxMessage) {
            $message .= __('Message:') . PHP_EOL;
            $message .= __('    Name: ') . $avataxMessage->getName() . PHP_EOL;
            $message .= __('    Summary: ') . $avataxMessage->getSummary() . PHP_EOL;
            $message .= __('    Details: ') . $avataxMessage->getDetails() . PHP_EOL;
            $message .= __('    RefersTo: ') . $avataxMessage->getRefersTo() . PHP_EOL;
            $message .= __('    Severity: ') . $avataxMessage->getSeverity() . PHP_EOL;
            $message .= __('    Source: ') . $avataxMessage->getSource() . PHP_EOL;
        }

        return $message;
    }
}
