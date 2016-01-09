<?php
namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

/**
 * Class Save
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
