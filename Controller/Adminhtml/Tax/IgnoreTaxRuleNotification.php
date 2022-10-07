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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class IgnoreTaxRuleNotification
 */
/**
 * @codeCoverageIgnore
 */
class IgnoreTaxRuleNotification extends Classes
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    /**
     * Set tax ignore notification flag and redirect back
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $path = \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_ADMIN_NOTIFICATION_IGNORE_NATIVE_TAX_RULES;
            $this->_objectManager->get('Magento\Config\Model\ResourceModel\Config')
                ->saveConfig($path, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->messageManager->addSuccess('Notification successfully ignored');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        // clear the block html cache
        $this->cacheTypeList->cleanType('config');
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'block_html']);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setRefererUrl();
    }
}
