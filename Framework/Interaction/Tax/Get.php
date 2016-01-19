<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax;

use AvaTax\GetTaxRequest;
use AvaTax\GetTaxResult;
use AvaTax\LineFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Cacheable\TaxService;
use ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Helper\Config;
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
     * @param TaxService $taxService
     */
    public function __construct(
        TaxCalculation $taxCalculation,
        Address $interactionAddress,
        Tax $interactionTax,
        LineFactory $lineFactory,
        Config $config,
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
     * @throws \ClassyLlama\AvaTax\Exception\TaxCalculationException
     */
    public function processSalesObject($object)
    {
        $taxService = $this->taxService;
        try {
            /** @var $getTaxRequest GetTaxRequest */
            $getTaxRequest = $this->interactionTax->getGetTaxRequestForSalesObject($object);
        } catch (\Exception $e) {
            $message = __('Error while building the request to send to AvaTax. ');
            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                    'entity_id' => $object->getEntityId(),
                    'object_class' => get_class($object),
                    'exception' => sprintf(
                        'Exception message: %s%sTrace: %s',
                        $e->getMessage(),
                        "\n",
                        $e->getTraceAsString()
                    ),
                ]
            );
            throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message . $e->getMessage(), $e->getCode(), $e);
        }

        if (is_null($getTaxRequest)) {
            $message = '$getTaxRequest was empty so not running getTax request.';
            $this->avaTaxLogger->warning($message);
            throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
        }

        try {
            $getTaxResult = $taxService->getTax($getTaxRequest);
            if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
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

                throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
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

            throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
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
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface[]
     * @throws \ClassyLlama\AvaTax\Exception\TaxCalculationException
     * @throws \Exception
     */
    public function getTaxDetailsForQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $baseTaxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $taxService = $this->taxService;
        try {
            // Total quantity of an item can be determined by multiplying parent * child quantity, so it's necessary
            // to calculate total quantities on a list of all items
            $this->taxCalculation->calculateTotalQuantities($taxQuoteDetails->getItems());
            $this->taxCalculation->calculateTotalQuantities($baseTaxQuoteDetails->getItems());

            // Taxes need to be calculated on the base prices/amounts, not the current currency prices. As a result of this,
            // only the $baseTaxQuoteDetails will have taxes calculated for it. The taxes for the current currency will be
            // calculated by multiplying the base tax rates * currency conversion rate.
            /** @var $getTaxRequest GetTaxRequest */
            $getTaxRequest = $this->interactionTax
                ->getGetTaxRequestForQuote($quote, $baseTaxQuoteDetails, $shippingAssignment);

            if (is_null($getTaxRequest)) {
                $message = __('$quote was empty or address was not valid so not running getTax request.');
                throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
            }

            $getTaxResult = $taxService->getTax($getTaxRequest, true);
            if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {

                $store = $quote->getStore();
                $taxDetails =
                    $this->taxCalculation->calculateTaxDetails($taxQuoteDetails, $getTaxResult, false, $store);
                $baseTaxDetails =
                    $this->taxCalculation->calculateTaxDetails($baseTaxQuoteDetails, $getTaxResult, true, $store);

                return [
                    self::KEY_TAX_DETAILS => $taxDetails,
                    self::KEY_BASE_TAX_DETAILS => $baseTaxDetails
                ];
            } else {
                $message = __('Bad result code: %1', $getTaxResult->getResultCode());
                $this->avaTaxLogger->warning(
                    $message,
                    [ /* context */
                        'request' => var_export($getTaxRequest, true),
                        'result' => var_export($getTaxResult, true),
                    ]
                );
                throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
            }
        } catch (\SoapFault $exception) {
            $message = "Exception: \n";
            if ($exception) {
                $message .= $exception->faultstring;
            }
            $message .= $taxService->__getLastRequest() . "\n";
            $message .= $taxService->__getLastResponse() . "\n";
            $this->avaTaxLogger->error(
                "Exception: \n" . ($exception) ? $exception->faultstring: "",
                [ /* context */
                    'request' => var_export($taxService->__getLastRequest(), true),
                    'result' => var_export($taxService->__getLastResponse(), true),
                ]
            );
            throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->avaTaxLogger->error($message);
            throw new \ClassyLlama\AvaTax\Exception\TaxCalculationException($message);
        }
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
