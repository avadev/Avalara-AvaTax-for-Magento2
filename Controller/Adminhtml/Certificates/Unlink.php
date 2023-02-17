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

use Magento\Framework\App\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
class Unlink extends \Magento\Backend\App\Action
{
    const CERTIFICATES_RESOURCE = 'ClassyLlama_AvaTax::customer_certificates';

    /**
     * @var \ClassyLlama\AvaTax\Helper\CertificateUnlinkHelper
     */
    protected $certificateUnlinkHelper;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \ClassyLlama\AvaTax\Helper\CertificateUnlinkHelper $certificateUnlinkHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \ClassyLlama\AvaTax\Helper\CertificateUnlinkHelper $certificateUnlinkHelper
    )
    {
        parent::__construct($context);
        $this->certificateUnlinkHelper = $certificateUnlinkHelper;
    }

    /**
     * Action to delete a certificate.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        //Forward any action to delete helper where delete logic is contained.
        $this->certificateUnlinkHelper->unlink();

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            static::CERTIFICATES_RESOURCE
        );
    }
}