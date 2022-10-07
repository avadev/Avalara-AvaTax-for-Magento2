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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

/**
 * Class Save
 */
/**
 * @codeCoverageIgnore
 */
abstract class Save extends \Magento\Tax\Controller\Adminhtml\Tax
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = null;

    /**
     * Save status form processing
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $isNew = $this->getRequest()->getParam('is_new');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $taxClassId = $this->getRequest()->getParam('id');
            $taxClass = $this->taxClassDataObjectFactory->create()
                ->setClassId($taxClassId)
                ->setClassType($this->classType)
                ->setClassName($this->_processClassName((string)$this->getRequest()->getPost('class_name')))
                ->setAvataxCode((string)$this->getRequest()->getPost('avatax_code'));

            try {
                $this->taxClassRepository->save($taxClass);
                $this->messageManager->addSuccess(__('You saved the tax class.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('We can\'t add the tax class right now.')
                );
            }
            $this->_getSession()->setFormData($data);
            if ($isNew) {
                return $resultRedirect->setPath('*/*/newClass');
            } else {
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClassyLlama_AvaTax::manage_avatax');
    }
}
