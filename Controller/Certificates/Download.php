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

namespace ClassyLlama\AvaTax\Controller\Certificates;

use ClassyLlama\AvaTax\Helper\CertificateDownloadControllerHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Raw as ResultRaw;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\DataObject;

/**
 * Class Download
 * @package ClassyLlama\AvaTax\Controller\Certificates
 */
/**
 * @codeCoverageIgnore
 */
class Download extends Action
{
    /**
     * @var CertificateDownloadControllerHelper
     */
    protected $certificateDownloadControllerHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Download constructor.
     * @param CertificateDownloadControllerHelper $certificateDownloadControllerHelper
     * @param Context $context
     * @param Session $session
     */
    public function __construct(
        CertificateDownloadControllerHelper $certificateDownloadControllerHelper,
        Context $context,
        Session $session
    ) {
        parent::__construct($context);
        $this->certificateDownloadControllerHelper = $certificateDownloadControllerHelper;
        $this->session = $session;
    }

    /**
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->session->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * @return ResultRaw|ResultRedirect
     */
    public function execute(): ResultInterface
    {
        /** @var RawResult|null $result */
        $result = $this->certificateDownloadControllerHelper->getDownloadRawResult();
        if ($result instanceof RawResult) {
            return $result;
        } else {
            if (null === $result || ($result instanceof DataObject && $result->hasData('error'))) {
                $codeExplainInfo = __('Something went wrong, please check the log file for more information');

                if($result->getData('error')['code'] == '400'){
                    $codeExplainInfo = __('The certificate file can\'t be displayed. 
                    It hasn\'t been generated or upload to the AvaTax Service early.');
                };

                $this->messageManager->addError($codeExplainInfo);
            }
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
