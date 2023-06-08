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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Certificates;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\DataObject;

use ClassyLlama\AvaTax\Helper\CertificateDownloadControllerHelper;

/**
 * @codeCoverageIgnore
 */
class Download extends \Magento\Backend\App\Action
{
    protected $_publicActions = ['download'];

    const CERTIFICATES_RESOURCE = 'ClassyLlama_AvaTax::customer_certificates';

    /**
     * @var CertificateDownloadControllerHelper
     */
    protected $certificateDownloadControllerHelper;

    /**
     * @param CertificateDownloadControllerHelper $certificateDownloadControllerHelper
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        CertificateDownloadControllerHelper $certificateDownloadControllerHelper,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->certificateDownloadControllerHelper = $certificateDownloadControllerHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            static::CERTIFICATES_RESOURCE
        );
    }

    /**
     * @return RawResult|\Magento\Framework\Controller\Result\Redirect|null
     */
    public function execute()
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
