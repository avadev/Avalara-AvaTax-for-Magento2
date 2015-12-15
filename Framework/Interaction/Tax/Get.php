<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax;

use AvaTax\GetTaxRequest;
use AvaTax\LineFactory;
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
     * @var null
     */
    protected $errorMessage = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

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
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        TaxCalculation $taxCalculation,
        Address $interactionAddress,
        Tax $interactionTax,
        LineFactory $lineFactory,
        Config $config,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->interactionAddress = $interactionAddress;
        $this->interactionTax = $interactionTax;
        $this->lineFactory = $lineFactory;
        $this->config = $config;
        $this->avaTaxLogger = $avaTaxLogger;
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
        $taxService = $this->interactionTax->getTaxService();

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

}
