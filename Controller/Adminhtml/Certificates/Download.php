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

use ClassyLlama\AvaTax\Helper\CertificateDownloadControllerHelper;

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
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        return $this->certificateDownloadControllerHelper->getDownloadRawResult();
    }
}
