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

/**
 * @codeCoverageIgnore
 */
class Delete extends \ClassyLlama\AvaTax\Controller\Adminhtml\CrossBorderType
{
    /**
     * @var CrossBorderTypeRepositoryInterface
     */
    protected $crossBorderTypeRepository;

    /**
     * @param CrossBorderTypeRepositoryInterface  $crossBorderTypeRepository
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     */
    public function __construct(
        CrossBorderTypeRepositoryInterface $crossBorderTypeRepository,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        parent::__construct($context, $coreRegistry);

        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                // init model and delete
                $this->crossBorderTypeRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Cross Border Type.'));

                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());

                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Cross Border Type to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
