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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\CrossBorderType;

use ClassyLlama\AvaTax\Api\CrossBorderTypeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Helper\ApiLog;

/**
 * @codeCoverageIgnore
 */
class Edit extends \ClassyLlama\AvaTax\Controller\Adminhtml\CrossBorderType
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CrossBorderTypeRepositoryInterface
     */
    protected $crossBorderTypeRepository;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param CrossBorderTypeRepositoryInterface         $crossBorderTypeRepository
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ApiLog $apiLog
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CrossBorderTypeRepositoryInterface $crossBorderTypeRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ApiLog $apiLog
    )
    {
        parent::__construct($context, $coreRegistry);

        $this->resultPageFactory = $resultPageFactory;
        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
        $this->apiLog = $apiLog;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = null;

        if($id) {
            try {
                $model = $this->crossBorderTypeRepository->getById($id);

                $this->coreRegistry->register('classyllama_avatax_crossbordertype', $model);
            } catch (LocalizedException $e) {
                $debugLogContext = [];
                $debugLogContext['message'] = $e->getMessage();
                $debugLogContext['source'] = 'EditCrossBorderType';
                $debugLogContext['operation'] = 'Controller_Adminhtml_CrossborderType_Edit';
                $debugLogContext['function_name'] = 'execute';
                $this->apiLog->debugLog($debugLogContext);
                $this->messageManager->addErrorMessage(__('This Cross Border Type no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Cross Border Type') : __('New Cross Border Type'),
            $id ? __('Edit Cross Border Type') : __('New Cross Border Type')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Cross Border Types'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model !== null ? $model->getType() : __('New Cross Border Type')
        );

        return $resultPage;
    }
}
