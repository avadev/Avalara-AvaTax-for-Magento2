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

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest;

use ClassyLlama\AvaTax\Model\Factory\LinkCustomersModelFactory;
use ClassyLlama\AvaTax\Api\RestCustomerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Customer as CustomerHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Customer extends Rest implements RestCustomerInterface
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LinkCustomersModelFactory
     */
    protected $customersModelFactory;

    /**
     * @param CustomerHelper $customerHelper
     * @param Config $config
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param LinkCustomersModelFactory $customersModelFactory
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Config $config,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        LinkCustomersModelFactory $customersModelFactory
    )
    {
        parent::__construct($logger, $dataObjectFactory, $clientPool);

        $this->customerHelper = $customerHelper;
        $this->config = $config;
        $this->customersModelFactory = $customersModelFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getCertificatesList(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient($isProduction, $scopeId, $scopeType);

        if ($request->getData('customer_code')) {
            throw new \InvalidArgumentException('Must include a request with customer id');
        }

        $clientResult = $client->listCertificatesForCustomer(
            $this->config->getCompanyId($scopeId, $scopeType),
            $this->customerHelper->getCustomerCode($request->getData('customer_id'), null, $scopeId),
            $request->getData('include'),
            $request->getData('filter'),
            $request->getData('top'),
            $request->getData('skip'),
            $request->getData('order_by')
        );

        $this->validateResult($clientResult, $request);

        $certificates = $this->formatResult($clientResult)->getValue();

        return $certificates;
    }

    /**
     * {@inheritDoc}
     */
    public function downloadCertificate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient($isProduction, $scopeId, $scopeType);

        return $client->downloadCertificateImage(
            $this->config->getCompanyId($scopeId, $scopeType),
            $request->getData('id'),
            $request->getData('page'),
            $request->getData('type')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCertificate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        /** @var \Avalara\AvaTaxClient $client */
        $client = $this->getClient($isProduction, $scopeId, $scopeType);

        try {
            $customerId = $this->customerHelper->getCustomerCode($request->getData('customer_id'), null, $scopeId);

            //unlink request requires a LinkCustomersModel which contains a string[] of all customer ids.
            /** @var \Avalara\LinkCustomersModel $customerModel */
            $customerModel = $this->customersModelFactory->create();
            $customerModel->customers = [$customerId];

            //Customer(s) must be unlinked from cert before it can be deleted.
            $unlinkResult = $client->unlinkCustomersFromCertificate(
                $this->config->getCompanyId($scopeId, $scopeType),
                $request->getData('id'),
                $customerModel
            );

            $this->validateResult($unlinkResult, $request);

        } catch (\Exception $e) {
            //Swallow this error. Continue to try and delete the cert.
            //If the deletion errors, then we'll notify the user that something has gone wrong.
        }

        //make deletion request.
        $result = $client->deleteCertificate(
            $this->config->getCompanyId($scopeId, $scopeType),
            $request->getData('id')
        );

        //A successful delete request results in an empty body. This means $result is an empty array.
        //However, the validateResult method can't handle this as a valid response.
        //This explicit check for an empty array is to fill that hole in the validation.
        if(is_array($result) && count($result) === 0) {
            return $result; //result is an empty array. No error was returned from request.
        }

        //Something went wrong, validate and handle error.
        $this->validateResult($result, $request);
        return $this->formatResult($result);
    }
}