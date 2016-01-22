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

namespace ClassyLlama\AvaTax\Controller\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\Proxy as InteractionGet;
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
     * @return Controller\Result\Raw
     */
    public function execute()
    {
        $contents = '';

        $id = $this->_request->getParam('id');
        $data = $this->invoiceRepository->get($id);

        $contents = $this->interactionGetTax->processSalesObject($data);

        /* @var $rawResult Controller\Result\Raw */
        $rawResult = $this->resultFactory->create(Controller\ResultFactory::TYPE_RAW);

        $rawResult->setContents("<pre>$contents</pre>");

        return $rawResult;
    }

}
