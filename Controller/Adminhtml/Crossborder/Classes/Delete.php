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

use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Helper\ApiLog;

/**
 * @codeCoverageIgnore
 */
class Delete extends \ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder\ClassesAbstract
{
    /**
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    protected $dataPersistor;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param Context $context
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param DataPersistorInterface $dataPersistor
     * @param ApiLog $apiLog
     */
    public function __construct(
        Context $context,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        DataPersistorInterface $dataPersistor,
        ApiLog $apiLog
    ) {
        parent::__construct($context);
        $this->crossBorderClassRepository = $crossBorderClassRepository;
        $this->dataPersistor = $dataPersistor;
        $this->apiLog = $apiLog;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            return $resultRedirect->setPath('*/*');
        }

        try {
            $this->crossBorderClassRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('You deleted the Cross Border Class'));
        } catch (NoSuchEntityException $e) {
            $debugLogContext = [];
            $debugLogContext['message'] = $e->getMessage();
            $debugLogContext['source'] = 'DeleteCrossBorderClass';
            $debugLogContext['operation'] = 'Controller_Adminhtml_Crossborder_Classes_Delete';
            $debugLogContext['function_name'] = 'execute';
            $this->apiLog->debugLog($debugLogContext);
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $debugLogContext = [];
            $debugLogContext['message'] = $e->getMessage();
            $debugLogContext['source'] = 'DeleteCrossBorderClass';
            $debugLogContext['operation'] = 'Controller_Adminhtml_Crossborder_Classes_Delete';
            $debugLogContext['function_name'] = 'execute';
            $this->apiLog->debugLog($debugLogContext);
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $debugLogContext = [];
            $debugLogContext['message'] = $e->getMessage();
            $debugLogContext['source'] = 'DeleteCrossBorderClass';
            $debugLogContext['operation'] = 'Controller_Adminhtml_Crossborder_Classes_Delete';
            $debugLogContext['function_name'] = 'execute';
            $this->apiLog->debugLog($debugLogContext);
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Cross Border Class'));
        }

        return $resultRedirect->setPath('*/*');
    }
}