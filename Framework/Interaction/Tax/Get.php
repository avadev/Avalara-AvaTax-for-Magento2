<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax;

use AvaTax\GetTaxRequest;
use AvaTax\LineFactory;
use AvaTax\Message;
use AvaTax\SeverityLevel;
use AvaTax\TaxLine;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\DataObject;

class Get
{
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

    public function __construct(
        Address $interactionAddress,
        Tax $interactionTax,
        LineFactory $lineFactory,
        Config $config
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->interactionTax = $interactionTax;
        $this->lineFactory = $lineFactory;
        $this->config = $config;
    }

    /**
     * Using test AvaTax file contents to do a sample validate test
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return string
     */
    public function getTax($data)
    {
        $response = '';

        $taxService = $this->interactionTax->getTaxService();

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->interactionTax->getGetTaxRequest($data);

        if (is_null($getTaxRequest)) {
            return '$data was empty or address was not valid so not running getTax request.' . "\n";
        }

// Results
        try {
            $getTaxResult = $taxService->getTax($getTaxRequest);
            $response .= 'GetTax is: ' . $getTaxResult->getResultCode() . "\n";
// Error Trapping
            if ($getTaxResult->getResultCode() == SeverityLevel::$Success) {
//Success - Display GetTaxResults to console
//Document Level Results
                $response .= "DocCode: " . $getTaxRequest->getDocCode() . "\n";
                $response .= "TotalAmount: " . $getTaxResult->getTotalAmount() . "\n";
                $response .= "TotalTax: " . $getTaxResult->getTotalTax() . "\n";
//Line Level Results (from TaxLines array class)
                /** @var $currentTaxLine TaxLine */
                foreach ($getTaxResult->getTaxLines() as $currentTaxLine) {
                    $response .= "     Line: " . $currentTaxLine->getNo() . " Tax: " . $currentTaxLine->getTax() . " TaxCode: " . $currentTaxLine->getTaxCode() . "\n";
//Line Level Results
                    foreach ($currentTaxLine->getTaxDetails() as $currentTaxDetails) {
                        $response .= "          Juris Type: " . $currentTaxDetails->getJurisType() . "; Juris Name: " . $currentTaxDetails->getJurisName() . "; Rate: " . $currentTaxDetails->getRate() . "; Amt: " . $currentTaxDetails->getTax() . "\n";
                    }
                    $response .="\n";
                }
//If NOT success - display error messages to console
            } else {
                /** @var $message Message */
                foreach ($getTaxResult->getMessages() as $message) {
                    $response .= $message->getName() . ": " . $message->getSummary() . "\n";
                }
            }
        } catch (\SoapFault $exception) {
            $message = "Exception: ";
            if ($exception) {
                $message .= $exception->faultstring;
            }
            $response .= $message . "\n";
            $response .= $taxService->__getLastRequest() . "\n";
            $response .= $taxService->__getLastResponse() . "\n   ";
        }
        return $response;
   }
}