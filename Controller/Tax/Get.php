<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
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
        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        $data = $this->orderRepository->get(2);
        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        $data = $this->quoteRepository->get(1);
        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        $data = $this->quoteRepository->get(2);
        $contents .= $this->interactionGetTax->getTax(
            $data
        );

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents($contents);

        return $rawResult;
    }
}
