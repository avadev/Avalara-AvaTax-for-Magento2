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
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing as BatchProcessing;
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
     * @var BatchProcessing
     */
    protected $batchProcessing;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService,
        \Magento\Tax\Api\Data\TaxClassInterfaceFactory $taxClassDataObjectFactory,
        BatchProcessing $batchProcessing
    )
    {
        $this->batchProcessing = $batchProcessing;
        parent::__construct($context, $taxClassService, $taxClassDataObjectFactory); 
    }
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
            $oldTaxCode = '';
            if (!$isNew && !empty($this->getRequest()->getParam('id'))) 
            {
                $id = $this->getRequest()->getParam('id');
                $taxClassCollection = $this->taxClassDataObjectFactory->create()->getCollection()->addFieldToFilter('class_id', $id)->getFirstItem();
                $recordData = $taxClassCollection->getData();
                if( count($recordData) > 0 )
                {
                    $oldTaxCode = $recordData['avatax_code'];
                }                
            }
            $taxClassId = $this->getRequest()->getParam('id');
            $taxClass = $this->taxClassDataObjectFactory->create()
                ->setClassId($taxClassId)
                ->setClassType($this->classType)
                ->setClassName($this->_processClassName((string)$this->getRequest()->getPost('class_name')))
                ->setAvataxCode((string)$this->getRequest()->getPost('avatax_code'));

            try {
                $this->taxClassRepository->save($taxClass);
                if (!$isNew && !empty($this->getRequest()->getParam('id'))) 
                {
                    $avatax_code = $this->getRequest()->getPost('avatax_code');
                    if( ( $oldTaxCode != $avatax_code ) )
                    {
                        $resyncResult = $this->batchProcessing->reSyncProductsWithNewTaxCode($taxClassId);
                        if($resyncResult)
                            $this->messageManager->addSuccess(__("Re Sync Initiated for all items with TaxCode : ".$avatax_code));
                    }
                }
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
