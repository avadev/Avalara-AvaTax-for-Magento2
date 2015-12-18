<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use AvaTax\GetTaxResult;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Get extends Action\Action
{
    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository = null;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository = null;

    public function __construct(
        Context $context,
        InteractionGet $interactionGetTax,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;

        parent::__construct($context);
    }

    /**
     * Test various getTax types
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return Controller\Result\Raw
     */
    public function execute()
    {
        $contents = '';

        $data = $this->orderRepository->get(1);
        $success = $this->interactionGetTax->getTaxDetailsForQuote($data);
        if ($success) {
            $contents .= $this->dumpTaxData($success) . "\n\n";
        }

        $data = $this->orderRepository->get(2);
        $success = $this->interactionGetTax->getTaxDetailsForQuote($data);
        if ($success) {
            $contents .= $this->dumpTaxData($success) . "\n\n";
        }

        $data = $this->quoteRepository->get(1);
        $success = $this->interactionGetTax->getTaxDetailsForQuote($data);
        if ($success) {
            $contents .= $this->dumpTaxData($success) . "\n\n";
        }

        $data = $this->quoteRepository->get(2);
        $success = $this->interactionGetTax->getTaxDetailsForQuote($data);
        if ($success) {
            $contents .= $this->dumpTaxData($success) . "\n\n";
        }

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents($contents);

        return $rawResult;
    }

    protected function dumpTaxData(GetTaxResult $getTaxResult)
    {
        $response = 'GetTax is: ' . $getTaxResult->getResultCode() . "\n";
// Error Trapping
        if ($getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
//Success - Display GetTaxResults to console
//Document Level Results
            $response .= "DocCode: " . $getTaxResult->getDocCode() . "\n";
            $response .= "TotalAmount: " . $getTaxResult->getTotalAmount() . "\n";
            $response .= "TotalTax: " . $getTaxResult->getTotalTax() . "\n";
//Line Level Results (from TaxLines array class)
            /** @var $currentTaxLine \AvaTax\TaxLine */
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
            /** @var $message \AvaTax\Message */
            foreach ($getTaxResult->getMessages() as $message) {
                $response .= $message->getName() . ": " . $message->getSummary() . "\n";
            }
        }
        return $response;
    }
}
