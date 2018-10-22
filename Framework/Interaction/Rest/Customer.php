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
        $client->withCatchExceptions(false);

        if ($request->getData('customer_code')) {
            throw new \InvalidArgumentException('Must include a request with customer id');
        }

        $clientResult = null;

        try {
            $clientResult = $client->listCertificatesForCustomer(
                $this->config->getCompanyId($scopeId, $scopeType),
                $this->customerHelper->getCustomerCode($request->getData('customer_id'), null, $scopeId),
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            $this->handleException($clientException, $request);
        }

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

        // TODO: error handling?
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
        $client->withCatchExceptions(false);

        try {
            $customerId = $this->customerHelper->getCustomerCode($request->getData('customer_id'), null, $scopeId);

            //unlink request requires a LinkCustomersModel which contains a string[] of all customer ids.
            /** @var \Avalara\LinkCustomersModel $customerModel */
            $customerModel = $this->customersModelFactory->create();
            $customerModel->customers = [$customerId];

            //Customer(s) must be unlinked from cert before it can be deleted.
            $client->unlinkCustomersFromCertificate(
                $this->config->getCompanyId($scopeId, $scopeType),
                $request->getData('id'),
                $customerModel
            );

        } catch (\Exception $e) {
            //Swallow this error. Continue to try and delete the cert.
            //If the deletion errors, then we'll notify the user that something has gone wrong.
        }

        $result = null;

        try {
            //make deletion request.
            $result = $client->deleteCertificate(
                $this->config->getCompanyId($scopeId, $scopeType),
                $request->getData('id')
            );
        } catch(\GuzzleHttp\Exception\ClientException $clientException) {
            $this->handleException($clientException, $request);
        }

        return $this->formatResult($result);
    }
}