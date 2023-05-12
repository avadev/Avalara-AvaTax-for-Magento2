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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder\Classes;

use ClassyLlama\AvaTax\Exception\InvalidTypeException;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;
use ClassyLlama\AvaTax\Helper\ApiLog;

/**
 * @codeCoverageIgnore
 */
class Edit extends \ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder\ClassesAbstract
{
    /**
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param Context $context
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param Registry $coreRegistry
     * @param ApiLog $apiLog
     */
    public function __construct(
        Context $context,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        Registry $coreRegistry,
        ApiLog $apiLog
    ) {
        parent::__construct($context);
        $this->crossBorderClassRepository = $crossBorderClassRepository;
        $this->coreRegistry = $coreRegistry;
        $this->apiLog = $apiLog;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = parent::execute();

        $classId = (int) $this->getRequest()->getParam('id');

        if ($classId) {
            try {
                $crossBorderClass = $this->crossBorderClassRepository->getById($classId);
            } catch (NoSuchEntityException $e) {
                $debugLogContext = [];
                $debugLogContext['message'] = $e->getMessage();
                $debugLogContext['source'] = 'EditCrossBorderClass';
                $debugLogContext['operation'] = 'Controller_Adminhtml_Crossborder_Classes_Edit';
                $debugLogContext['function_name'] = 'execute';
                $this->apiLog->debugLog($debugLogContext);
                $this->messageManager->addExceptionMessage($e, __('Cross Border Class does not exist'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            } catch (\Exception $e) {
                $debugLogContext = [];
                $debugLogContext['message'] = $e->getMessage();
                $debugLogContext['source'] = 'EditCrossBorderClass';
                $debugLogContext['operation'] = 'Controller_Adminhtml_Crossborder_Classes_Edit';
                $debugLogContext['function_name'] = 'execute';
                $this->apiLog->debugLog($debugLogContext);
                $this->messageManager->addExceptionMessage($e, __('An error occurred while loading the class'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }

            $this->coreRegistry->register('current_crossborder_class', $crossBorderClass);
        }

        return $pageResult;
    }
}
