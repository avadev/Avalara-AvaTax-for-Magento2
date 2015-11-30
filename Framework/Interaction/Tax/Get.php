<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Tax;

use AvaTax\GetTaxRequest;
use AvaTax\GetTaxResult;
use AvaTax\LineFactory;
use AvaTax\Message;
use AvaTax\SeverityLevel;
use AvaTax\TaxLine;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

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

    /**
     * @var null
     */
    protected $errorMessage = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    public function __construct(
        Address $interactionAddress,
        Tax $interactionTax,
        LineFactory $lineFactory,
        Config $config,
        AvaTaxLogger $avaTaxLogger
    ) {
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
     * @return bool|GetTaxResult
     */
    public function getTax($data)
    {
        $this->avaTaxLogger->debug('calling getTax');
//        $this->avaTaxLogger->info(
//            'sample message',
//            array( /* context */
//                'store_id' => 'the store id goes here',
//                'activity' => 'what kind of activity is SUPPOSED to be happening',
//                'source' => 'source of the activity',
//                'activity_status' => 'what is the status of the activity being logged',
//                'request' => 'request details',
//                'result' => 'result details',
//                'additional' => 'any additional context details to be logged'
//            )
//        );
        $taxService = $this->interactionTax->getTaxService();

        /** @var $getTaxRequest GetTaxRequest */
        $getTaxRequest = $this->interactionTax->getGetTaxRequest($data);

        if (is_null($getTaxRequest)) {
            // TODO: Possibly refactor all usages of setErrorMessage to throw exception instead so that this class can be stateless
            $this->setErrorMessage('$data was empty or address was not valid so not running getTax request.');
            $this->avaTaxLogger->warning(
                '$data was empty or address was not valid so not running getTax request.',
                array( /* context */
                    'activity' => 'building $getTaxRequest',
                    'source' => '\ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTax()',
                    'activity_status' => 'error'
                )
            );
            return false;
        }

        try {
            $getTaxResult = $taxService->getTax($getTaxRequest);

            if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
                $this->avaTaxLogger->info(
                    'getTax',
                    array( /* context */
                        'activity' => 'getTax',
                        'source' => '\ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTax()',
                        'activity_status' => 'success',
                        'request' => var_export($getTaxRequest),
                        'result' => var_export($getTaxResult),
                    )
                );
                return $getTaxResult;
            } else {
                // TODO: Generate better error message
                $this->setErrorMessage('Bad result code: ' . $getTaxResult->getResultCode());
                $this->avaTaxLogger->warning(
                    'Bad result code: ' . $getTaxResult->getResultCode(),
                    array( /* context */
                        'activity' => 'getTax',
                        'source' => '\ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTax()',
                        'activity_status' => 'error',
                        'request' => var_export($getTaxRequest),
                        'result' => var_export($getTaxResult),
                    )
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
                array( /* context */
                    'activity' => 'getTax',
                    'source' => '\ClassyLlama\AvaTax\Framework\Interaction\Tax\Get::getTax()',
                    'activity_status' => 'error',
                    'request' => var_export($taxService->__getLastRequest()),
                    'result' => var_export($taxService->__getLastResponse()),
                )
            );
        }
        return false;
    }

    /**
     * Set error message
     *
     * @return void
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
