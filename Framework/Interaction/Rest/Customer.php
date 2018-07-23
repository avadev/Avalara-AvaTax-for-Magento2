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
     * @param CustomerHelper    $customerHelper
     * @param Config            $config
     * @param LoggerInterface   $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool        $clientPool
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Config $config,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool
    )
    {
        parent::__construct($logger, $dataObjectFactory, $clientPool);

        $this->customerHelper = $customerHelper;
        $this->config = $config;
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
}