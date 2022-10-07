<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use ClassyLlama\AvaTax\Model\Customer\CustomerUpdate as CustomerUpdateService;
use ClassyLlama\AvaTax\Api\CustomerUpdateInterface;

/**
 * Class Update
 * @package ClassyLlama\AvaTax\Controller\Adminhtml\Customer
 */
/**
 * @codeCoverageIgnore
 */
class Update extends Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = "ClassyLlama_AvaTax::avalara_update_customer_information";

    /**
     * @var CustomerUpdateService
     */
    private $customerUpdateService;

    /**
     * Update constructor.
     * @param Action\Context $context
     * @param CustomerUpdateService $customerUpdateService
     */
    public function __construct(Action\Context $context, CustomerUpdateService $customerUpdateService)
    {
        parent::__construct($context);
        $this->customerUpdateService = $customerUpdateService;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $this->_eventManager->dispatch('avalara_customer_before_update', [
            'customer_id' => $customerId
        ]);
        /** @var CustomerUpdateInterface $updateResult */
        $updateResult = $this->customerUpdateService->updateCustomerInformation($customerId);
        $this->_eventManager->dispatch('avalara_customer_after_update', [
            'customer_id' => $customerId
        ]);
        /** @var array $result */
        $result = $updateResult->customerUpdateResults->getData('result');
        
        if (!empty($result)) {
            if (isset($result['error_status']) && false === (bool)$result['error_status']) {
                $this->messageManager->addSuccessMessage(__('Customer with Id: %1 was successfully updated at the Avalara service.', $customerId));
            }
            if (isset($result['error_status']) && true === (bool)$result['error_status']) {
                $this->messageManager->addErrorMessage(__(($result['message'] ? $result['message'] : 'Error happened, please check log file.')));
            }
        }
        
        return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
    }
}
