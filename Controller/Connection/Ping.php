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
                /** @var $messages \AvaTax\Message */
                foreach ($pingResult->getMessages() as $messages)
                {
                    echo $messages->getName() . ": " . $messages->getSummary() . "\n";
                }
            } else
            {
                echo 'Ping Version is: ' . $pingResult->getVersion() . "\n";
                echo 'TransactionID is: ' . $pingResult->getTransactionId() . "\n\n";
            }
        } catch (\SoapFault $exception)
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
