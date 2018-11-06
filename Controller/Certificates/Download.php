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

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CertificateDownloadControllerHelper
     */
    protected $certificateDownloadControllerHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * Download constructor.
     *
     * @param CertificateDownloadControllerHelper $certificateDownloadControllerHelper
     * @param \Magento\Backend\App\Action\Context $context* @param \Magento\Customer\Model\Session                  $session
     */
    public function __construct(
        CertificateDownloadControllerHelper $certificateDownloadControllerHelper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Session $session
    )
    {
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