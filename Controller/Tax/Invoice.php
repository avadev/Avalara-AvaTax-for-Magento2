<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class Invoice extends Action\Action
{
    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository = null;

    public function __construct(
        Context $context,
        InteractionGet $interactionGetTax,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->invoiceRepository = $invoiceRepository;

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

        $data = $this->invoiceRepository->get(4);

        $contents = $this->interactionGetTax->processInvoice($data);

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents("<pre>$contents</pre>");

        return $rawResult;
    }

}
