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

use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\RequestInterface;

/**
 * @codeCoverageIgnore
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Customer\Model\Session            $session
     * @param \Magento\Framework\UrlFactory              $urlFactory
     * @param DocumentManagementConfig                   $documentManagementConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\UrlFactory $urlFactory,
        DocumentManagementConfig $documentManagementConfig
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->documentManagementConfig = $documentManagementConfig;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->session->authenticate() || !$this->documentManagementConfig->isEnabled()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_response->setRedirect($this->urlFactory->create()->getUrl('customer/account'));
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Add the ID to the registry to provide data to the ViewModel
        $this->coreRegistry->register(
            RegistryConstants::CURRENT_CUSTOMER_ID,
            $this->session->getCustomerId()
        );

        return $this->resultPageFactory->create();
    }
}
