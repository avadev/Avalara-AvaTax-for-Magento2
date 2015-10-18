<?php

namespace ClassyLlama\AvaTax\Controller\Connection;

use AvaTax\SeverityLevel;
use AvaTax\TaxServiceSoap;
use Magento\Framework\App\Action;

class Ping extends Action\Action
{
    public function __construct(

    ) {

    }

    public function execute()
    {
        $taxSvc = new TaxServiceSoap('Development');

        try
        {
            $pingResult = $taxSvc->ping("");
            echo 'Ping ResultCode is: ' . $pingResult->getResultCode() . "\n";
            if ($pingResult->getResultCode() != SeverityLevel::$Success)
            {
                foreach ($pingResult->Messages() as $messages)
                {
                    echo $messages->Name() . ": " . $messages->Summary() . "\n";
                }
            } else
            {
                echo 'Ping Version is: ' . $pingResult->getVersion() . "\n";
                echo 'TransactionID is: ' . $pingResult->getTransactionId() . "\n\n";
            }
        } catch (SoapFault $exception)
        {
            $messages = "Exception: ";
            if ($exception)
            {
                $messages .= $exception->faultstring;
            }
            echo $messages . "\n";
            echo $taxSvc->__getLastRequest() . "\n";
            echo $taxSvc->__getLastResponse() . "\n   ";
        }

    }
}