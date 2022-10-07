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

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper
     */
    protected $certificateDeleteHelper;

    /**
     * Delete constructor.
     * @param Context $context
     * @param \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper
     */
    public function __construct(
        Context $context,
        \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper
    )
    {
        parent::__construct($context);
        $this->certificateDeleteHelper = $certificateDeleteHelper;
    }

    /**
     * Action to delete a certificate.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        //Forward any action to delete helper where delete logic is contained.
        $this->certificateDeleteHelper->delete();

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}