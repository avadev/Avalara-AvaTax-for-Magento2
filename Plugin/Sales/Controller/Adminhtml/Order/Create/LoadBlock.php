<?php

namespace ClassyLlama\AvaTax\Plugin\Sales\Controller\Adminhtml\Order\Create;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use Magento\Framework\App\Action\Context;

class LoadBlock
{
    protected $customsConfig;
    protected $objectManager;
    protected $orderCreateModel;
    protected $extensionFactory;

    public function __construct(
        CustomsConfig $customsConfig,
        Context $context,
        \Magento\Quote\Api\Data\CartExtensionFactory $extensionFactory
    )
    {
        $this->customsConfig = $customsConfig;
        $this->objectManager = $context->getObjectManager();
        $this->orderCreateModel = $this->objectManager->get(\Magento\Sales\Model\AdminOrder\Create::class);
        $this->extensionFactory = $extensionFactory;
    }

    public function beforeExecute(\Magento\Sales\Controller\Adminhtml\Order\Create\LoadBlock $subject)
    {
        if(isset($subject->getRequest()->getPost('order')['override_importer_of_record'])) {
            $quote = $this->orderCreateModel->getQuote();
            $extensionAttribute = $quote->getExtensionAttributes() ?
                $quote->getExtensionAttributes() :
                $this->extensionFactory->create();
            if(!$extensionAttribute->getOverrideImporterOfRecord()) {
                $extensionAttribute->setOverrideImporterOfRecord(
                    $subject->getRequest()->getPost('order')['override_importer_of_record']
                );
                $quote->setExtensionAttributes($extensionAttribute);
            }
            $this->orderCreateModel->saveQuote();
        }
        return [];
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}