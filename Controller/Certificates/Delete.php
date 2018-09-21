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

namespace ClassyLlama\AvaTax\Controller\Certificates;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $customerRest;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    )
    {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Action to delete a certificate.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {
            $customerId = $this->getRequest()->getParam('customer_id');
            $certificateId = $this->getRequest()->getParam('certificate_id');
            $storeId = null;

            // If we have specified a customer ID, use the store that user is associated with, otherwise default to session
            if ($customerId !== null) {
                $customerModel = $this->customerRepository->getById($customerId);
                $storeId = $customerModel->getStoreId();
            }

            //try to delete cert. Any/all errors during process caught below.
            $this->customerRest->deleteCertificate(
                $this->dataObjectFactory->create(
                    ['data' => [
                        'id' => $certificateId,
                        'customer_id' => $customerId
                    ]]
                ),
                null,
                $storeId
            );

            $this->messageManager->addSuccessMessage(__('Your certificate has been successfully deleted.'));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem deleting your certificate.'));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}