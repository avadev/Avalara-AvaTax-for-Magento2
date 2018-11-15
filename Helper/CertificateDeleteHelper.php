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

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class CertificateDeleteHelper extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \ClassyLlama\AvaTax\Model\TaxCache
     */
    protected $taxCache;

    /**
     * CertificateDeleteHelper constructor.
     *
     * @param Context                                           $context
     * @param \Magento\Framework\App\RequestInterface           $request
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface     $customerRest
     * @param \Magento\Framework\DataObjectFactory              $dataObjectFactory
     * @param \ClassyLlama\AvaTax\Model\TaxCache                $taxCache
     * @param \Magento\Framework\Message\ManagerInterface       $messageManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \ClassyLlama\AvaTax\Model\TaxCache $taxCache,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->messageManager = $messageManager;
        $this->taxCache = $taxCache;
    }

    /**
     * Handle a certificate delete request.
     */
    public function delete()
    {
        try {
            $customerId = $this->request->getParam('customer_id');
            $certificateId = $this->request->getParam('certificate_id');
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
            // Ensure that all caches for tax calculation have been cleared for this customer to ensure accurate tax
            // during checkout
            $this->taxCache->clearCacheByCustomerId($customerId);

            $this->messageManager->addSuccessMessage(__('Your certificate has been successfully deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem deleting your certificate.'));
        }
    }
}
