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
