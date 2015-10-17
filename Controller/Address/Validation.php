<?php

namespace ClassyLlama\AvaTax\Controller\Address;

use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Framework\App\Action;
use Magento\Framework\Controller;
use Magento\Framework\App\Action\Context;

class Validation extends Action\Action
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    public function __construct(
        ValidationInteraction $validationInteraction,
        Context $context
    ) {
        $this->validationInteraction = $validationInteraction;
        parent::__construct($context);
    }

    /**
     * Test Validate Address Functionality
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return Controller\Result\Raw
     */
    public function execute()
    {
        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);
        $rawResult->setContents($this->validationInteraction->validateAddress());

        return $rawResult;
    }
}