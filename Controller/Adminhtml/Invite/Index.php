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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Invite;

use Magento\Backend\App\Action;

/**
 * @codeCoverageIgnore
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $restCustomer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $restCustomer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param Action\Context $context
     */
    public function __construct(
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $restCustomer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Action\Context $context
    )
    {
        parent::__construct($context);
        $this->restCustomer = $restCustomer;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $parameters = $this->_request->getParams();

        // If we were not given a proper customer id to invite, we can't get customer information
        if (!isset($parameters['customer_id']) || !is_numeric($parameters['customer_id'])) {
            $this->messageManager->addErrorMessage('Unable to create customer, malformed data');

            return $resultRedirect->setPath('*/*/*');
        }

        $customer = $this->customerRepository->getById($parameters['customer_id']);

        // We can only create a customer with a default billing address
        if (!(bool)$customer->getDefaultBilling()) {
            $this->messageManager->addErrorMessage(
                'Customer does not have a default billing address, please set a default billing address in order to create an customer record in AvaTax'
            );

            return $resultRedirect->setPath('customer/index/edit', ['id' => $parameters['customer_id']]);
        }

        // Ensure that we have a customer in AvaTax
        try {
            $this->restCustomer->createCustomer($customer);
        } catch (\Throwable $throwable) {
            $responseObject = null;

            if ($throwable->getPrevious() instanceof \GuzzleHttp\Exception\RequestException) {
                $responseObject = json_decode((string)$throwable->getPrevious()->getResponse()->getBody(), true);
            }

            // We will swallow any duplicate entry message. This saves an AvaTax api call
            if ($responseObject === null || !isset($responseObject['error']['code']) || $responseObject['error']['code'] !== 'DuplicateEntry') {
                $this->messageManager->addErrorMessage('We were unable to create the customer in AvaTax.');

                return $resultRedirect->setPath('customer/index/edit', ['id' => $parameters['customer_id']]);
            }
        }

        // Send the invite request
        try {
            $this->restCustomer->sendCertExpressInvite($customer);
        } catch (\Throwable $throwable) {
            $this->messageManager->addErrorMessage('We were unable to send an invite to customer.');

            return $resultRedirect->setPath('customer/index/edit', ['id' => $parameters['customer_id']]);
        }

        $this->messageManager->addSuccessMessage(
            'Avalara has emailed the customer so they can add an exemption certificate.'
        );

        return $resultRedirect->setPath('customer/index/edit', ['id' => $parameters['customer_id']]);
    }
}
