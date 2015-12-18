<?php

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get as InteractionGet;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class Creditmemo extends Action\Action
{
    /**
     * @var Get
     */
    protected $interactionGetTax = null;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository = null;

    public function __construct(
        Context $context,
        InteractionGet $interactionGetTax,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->interactionGetTax = $interactionGetTax;
        $this->creditmemoRepository = $creditmemoRepository;

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

        $id = $this->_request->getParam('id');

        // Don't test any credit memos before 14 as the base_tax_amounts are off
        $data = $this->creditmemoRepository->get($id);

        $contents = $this->interactionGetTax->processSalesObject($data);

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents("<pre>$contents</pre>");

        return $rawResult;
    }

}
