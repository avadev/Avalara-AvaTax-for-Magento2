<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

class Get extends Action\Action
{
    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFactory = null;

    public function __construct(
        Context $context,
        InteractionGet $interactionGetTax,
        OrderInterfaceFactory $orderFactory
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->orderFactory = $orderFactory;
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

        $order3 = $this->orderFactory->create()->load(3);
        $data = $order3;

        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        $order1 = $this->orderFactory->create()->load(1);
        $data = $order1;

        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        $order2 = $this->orderFactory->create()->load(2);
        $data = $order2;

        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents($contents);

        return $rawResult;
    }
}
