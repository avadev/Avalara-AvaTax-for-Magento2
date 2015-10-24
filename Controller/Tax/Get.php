<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;

class Get extends Action\Action
{
    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    public function __construct(
        Context $context,
        InteractionGet $interactionGetTax
    ) {
        $this->interactionGetTax = $interactionGetTax;
        parent::__construct($context);
    }

    /**
     * TODO: Change params passed in to getTax to make them match the method signature
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return Controller\Result\Raw
     */
    public function execute()
    {
        $contents = '';

        $contents .= $this->interactionGetTax->getTax(
            [
                'line1' => '45 Fremont Street',
                'city' => 'San Francisco',
                'region' => 'CA',
                'postalCode' => '94105-2204',
                'country' => 'US',
            ],
            [
                'line1' => '118 N Clark St',
                'line2' => 'Suite 100',
                'line3' => 'ATTN Accounts Payable',
                'city' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60602-1304',
                'country' => 'US',
            ],
            [
                'line1' => '100 Ravine Lane',
                'line2' => 'Suite 100',
                'city' => 'Bainbridge Island',
                'region' => 'WA',
                'postalCode' => '98110',
                'country' => 'US',
            ]
        );

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents($contents);

        return $rawResult;
    }
}
