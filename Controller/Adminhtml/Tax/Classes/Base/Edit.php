<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

use ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

/**
 * Adminhtml controller
 */
abstract class Edit extends Classes
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = null;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->taxClassRepository = $taxClassRepository;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $taxClass = $this->taxClassRepository->get($this->getRequest()->getParam('id'));
            $this->coreRegistry->register('current_tax_class', $taxClass);
            /** @var Page $pageResult */
            $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $pageResult->setActiveMenu('ClassyLlama_AvaTax::avatax_tax_classes_' . \strtolower($this->classType));
            $pageResult->getConfig()->getTitle()->prepend(__('Edit ' . \ucfirst(\strtolower($this->classType)) . ' Tax Class'));
            return $pageResult;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__('We can\'t find this tax class.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
    }
}
