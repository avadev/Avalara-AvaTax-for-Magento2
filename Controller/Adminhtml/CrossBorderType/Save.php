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
use ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * @codeCoverageIgnore
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var CrossBorderTypeRepositoryInterface
     */
    protected $crossBorderTypeRepository;

    /**
     * @var CrossBorderTypeInterfaceFactory
     */
    protected $crossBorderTypeInterfaceFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param CrossBorderTypeRepositoryInterface $crossBorderTypeRepository
     * @param CrossBorderTypeInterfaceFactory $crossBorderTypeInterfaceFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        CrossBorderTypeRepositoryInterface $crossBorderTypeRepository,
        CrossBorderTypeInterfaceFactory $crossBorderTypeInterfaceFactory
    )
    {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
        $this->crossBorderTypeInterfaceFactory = $crossBorderTypeInterfaceFactory;
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
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');

            try {
                $model = $id ? $this->crossBorderTypeRepository->getById($id)
                    : $this->crossBorderTypeInterfaceFactory->create();

                if (!$model->getEntityId() && $id) {
                    $this->messageManager->addErrorMessage(__('This Cross Border Type no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }

                $model->setData($data);
                $this->crossBorderTypeRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the Cross Border Type.'));
                $this->dataPersistor->clear('classyllama_avatax_crossbordertype');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Cross Border Type.')
                );
            }

            $this->dataPersistor->set('classyllama_avatax_crossbordertype', $data);

            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
