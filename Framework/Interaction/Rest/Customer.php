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
     * @var \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory
     */
    protected $customerModelFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @param CustomerHelper $customerHelper
     * @param Config $config
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory $customerModelFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Config $config,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory $customerModelFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    )
    {
        parent::__construct($logger, $dataObjectFactory, $clientPool);

        $this->customerHelper = $customerHelper;
        $this->config = $config;
        $this->customerModelFactory = $customerModelFactory;
        $this->addressRepository = $addressRepository;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateCustomer(
        $customer,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $customerModel = $this->buildCustomerModel($customer, $scopeId);

        $client = $this->getClient($isProduction, $scopeId, $scopeType);

        return $client->updateCustomer(
            $this->config->getCompanyId($scopeId, $scopeType),
            $this->customerHelper->getCustomerCode($customer->getId(), null, $scopeId),
            $customerModel
        );
    }

    /**
     * Given a Magento customer, build an Avalara CustomerModel for request.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null $scopeId
     * @return \Avalara\CustomerModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function buildCustomerModel(\Magento\Customer\Api\Data\CustomerInterface $customer, $scopeId = null)
    {

        /** @var \Avalara\CustomerModel $customerModel */
        $customerModel = $this->customerModelFactory->create();

        $customerModel->customerCode = $this->customerHelper->getCustomerCode($customer->getId(), null, $scopeId);
        $customerModel->name = $customer->getFirstname() . ' ' . $customer->getLastname();
        $customerModel->emailAddress = $customer->getEmail();

        if($customer->getDefaultBilling()) {
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            $address = $this->addressRepository->getById($customer->getDefaultBilling());

            $customerModel->phoneNumber = $address->getTelephone();

            $customerModel->line1 = $address->getStreet(); //todo handle 2 street line
            $customerModel->city = $address->getCity();
            $customerModel->region = $address->getRegion()->getRegionCode();
            $customerModel->country = $address->getCountryId();
            $customerModel->postalCode = $address->getPostcode();
        }

        return $customerModel;
    }
}