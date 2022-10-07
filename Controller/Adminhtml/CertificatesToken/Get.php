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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\CertificatesToken;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use Magento\Framework\DataObject;

/**
 * @codeCoverageIgnore
 */
class Get extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultPageFactory;

    /**
     * @var \ClassyLlama\AvaTax\Model\Token
     */
    protected $tokenModel;

    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory
     * @param \ClassyLlama\AvaTax\Model\Token                  $tokenModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        \ClassyLlama\AvaTax\Model\Token $tokenModel
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->tokenModel = $tokenModel;
    }

    /**
     * {@inheritDoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClassyLlama_AvaTax::customer_certificates');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $postValue = $request->getPostValue();
        $resultJson = $this->resultPageFactory->create();

        /** @var \ClassyLlama\AvaTax\Model\Data\SDKToken|string $tokenInfo */
        $tokenInfo = $this->tokenModel->getTokenForCustomerId($postValue['customer_id']);

        if (!is_string($tokenInfo)) {
            $tokenInfo = $tokenInfo->getData();
        }

        return $resultJson->setData($tokenInfo);
    }
}
