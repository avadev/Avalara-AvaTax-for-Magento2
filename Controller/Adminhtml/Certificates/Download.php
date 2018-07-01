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

class Download extends \Magento\Backend\App\Action
{
    protected $_publicActions = ['download'];

    /**
     * @var Download
     */
    protected $downloadController;

    /**
     * @param \ClassyLlama\AvaTax\Controller\Certificates\Download $downloadController
     * @param \Magento\Backend\App\Action\Context                  $context
     */
    public function __construct(
        \ClassyLlama\AvaTax\Controller\Certificates\Download $downloadController,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);

        $this->downloadController = $downloadController;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            \ClassyLlama\AvaTax\Block\Adminhtml\CustomerCertificates::CERTIFICATES_RESOURCE
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->downloadController->execute();
    }
}