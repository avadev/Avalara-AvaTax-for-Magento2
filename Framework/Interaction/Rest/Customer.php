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
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Exception\AvaTaxCustomerDoesNotExistException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Customer as CustomerHelper;
use ClassyLlama\AvaTax\Model\Factory\LinkCustomersModelFactory;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\ClientException as GuzzleHttpClientException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DataObject;

/**
 * Class Customer
 * @package ClassyLlama\AvaTax\Framework\Interaction\Rest
 */
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
     * @var \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory
     */
    protected $customerModelFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @param CustomerHelper                                         $customerHelper
     * @param Config                                                 $config
     * @param LoggerInterface                                        $logger
     * @param DataObjectFactory                                      $dataObjectFactory
     * @param ClientPool                                             $clientPool
     * @param \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory $customerModelFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface       $addressRepository
     * @param LinkCustomersModelFactory                              $customersModelFactory
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Config $config,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        \ClassyLlama\AvaTax\Model\Factory\CustomerModelFactory $customerModelFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        LinkCustomersModelFactory $customersModelFactory
    ) {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->customerHelper = $customerHelper;
        $this->config = $config;
        $this->customerModelFactory = $customerModelFactory;
        $this->addressRepository = $addressRepository;
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
    ) {
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);

        if ($request->getData('customer_code')) {
            throw new \InvalidArgumentException('Must include a request with customer id');
        }

        $clientResult = null;

        try {
            $clientResult = $client->listCertificatesForCustomer(
                $this->config->getCompanyId($scopeId, $scopeType),
                $this->customerHelper->getCustomerCodeByCustomerId($request->getData('customer_id'), null, $scopeId),
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // TODO: Possibly specifically handle no entity exception as an empty array of certificates?
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
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        /** @var \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper $client */
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        try {
            /** @var string|null $result */
            $result = $client->downloadCertificateImage(
                $this->config->getCompanyId($scopeId, $scopeType),
                $request->getData('id'),
                $request->getData('page'),
                $request->getData('type')
            );
            return $result;
        } catch (GuzzleHttpClientException $clientException) {
            throw $clientException;
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCertificate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        /** @var \Avalara\AvaTaxClient $client */
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);

        try {
            $customerId = $this->customerHelper->getCustomerCodeByCustomerId(
                $request->getData('customer_id'),
                null,
                $scopeId
            );

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
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException, $request, LOG_ERR, true);
        }

        return $this->formatResult($result);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer(
        $customer,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        // Client must be retrieved before any class from the /avalara/avataxclient/src/Models.php file is instantiated.
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        $customerModel = $this->buildCustomerModel($customer, $scopeId, $scopeType); // Instantiates an Avalara class.

        $response = null;

        try {
            $response = $client->updateCustomer(
                $this->config->getCompanyId($scopeId, $scopeType),
                $this->customerHelper->getCustomerCode($customer, null, $scopeId),
                $customerModel
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // Validate the response; pass the customer id for context in case of an error.
            $this->handleException(
                $clientException,
                $this->dataObjectFactory->create(['customer_id' => $customer->getId()]),
                LOG_ERR,
                true
            );
        }

        return $this->formatResult($response);
    }

    /**
     * {@inheritDoc}
     */
    public function createCustomer(
        $customer,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        // Client must be retrieved before any class from the /avalara/avataxclient/src/Models.php file is instantiated.
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        $customerModel = $this->buildCustomerModel($customer, $scopeId, $scopeType); // Instantiates an Avalara class.

        $response = null;

        try {
            $response = $client->createCustomers(
                $this->config->getCompanyId($scopeId, $scopeType),
                [$customerModel]
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // Validate the response; pass the customer id for context in case of an error.
            $this->handleException(
                $clientException,
                $this->dataObjectFactory->create(['customer_id' => $customer->getId()])
            );
        }

        return $this->formatResult($response);
    }

    /**
     * {@inheritDoc}
     */
    public function sendCertExpressInvite(
        $customer,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        // Client must be retrieved before any class from the /avalara/avataxclient/src/Models.php file is instantiated.
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);

        $response = null;

        try {
            $response = $client->createCertExpressInvitation(
                $this->config->getCompanyId($scopeId, $scopeType),
                $this->customerHelper->getCustomerCode($customer, null, $scopeId),
                [
                    'recipient' => $customer->getEmail(),
                    'coverLetterTitle' => 'STANDARD_REQUEST',
                    'deliveryMethod' => 'Email'
                ]
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // Validate the response; pass the customer id for context in case of an error.
            $this->handleException(
                $clientException,
                $this->dataObjectFactory->create(['customer_id' => $customer->getId()])
            );
        }

        return $this->formatResult($response);
    }

    /**
     * {@inheritDoc}
     */
    protected function handleException($exception, $request = null, $logMethod = LOG_ERR, $cacheThrowableException = false)
    {
        $isMissingCustomerException = false;
        if($cacheThrowableException) {
            try {
                $responseBody = $exception->getResponse()->getBody();
            } catch (\Throwable  $throwable) {
                throw new \Exception();
            }
        } else {
            $responseBody = $exception->getResponse()->getBody();
        }

        $responseObject = json_decode((string)$responseBody, true);

        // Customer calls where there is no customer should be suppressed as debug messages to avoid noisy errors
        if (isset($responseObject['error']['code']) && ($responseObject['error']['code'] === 'EntityNotFoundError' || ($responseObject['error']['code'] === 'CertificatesError' && array_reduce(
            $responseObject['error']['details'],
            function ($isMissingCustomer, $errorDetails) {
                return $isMissingCustomer || (string)$errorDetails['number'] === '1203';
            },
            false
                    )))) {
            $logMethod = LOG_DEBUG;
            $isMissingCustomerException = true;
        }

        try {
            parent::handleException($exception, $request, $logMethod);
        } catch (AvataxConnectionException $avataxConnectionException) {
            if ($isMissingCustomerException) {
                throw new AvaTaxCustomerDoesNotExistException(__($exception->getMessage()), $avataxConnectionException);
            }

            throw $avataxConnectionException;
        }
    }

    /**
     * Given a Magento customer, build an Avalara CustomerModel for request.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null                                         $scopeId
     * @param string                                       $scopeType
     *
     * @return \Avalara\CustomerModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function buildCustomerModel(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        /** @var \Avalara\CustomerModel $customerModel */
        $customerModel = $this->customerModelFactory->create();

        $customerModel->customerCode = $this->customerHelper->getCustomerCode($customer, null, $scopeId);
        $customerModel->name = "{$customer->getFirstname()} {$customer->getLastname()}";
        $customerModel->emailAddress = $customer->getEmail();
        $customerModel->companyId = $this->config->getCompanyId($scopeId, $scopeType);
        $customerModel->createdDate = $customer->getCreatedAt();
        $customerModel->modifiedDate = $customer->getUpdatedAt();

        // If a customer does not have a billing address, then no address updates will take place.
        if ($customer->getDefaultBilling()) {

            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            $address = $this->addressRepository->getById($customer->getDefaultBilling());

            if (isset($address->getStreet()[0])) {
                $customerModel->line1 = $address->getStreet()[0];
            }

            if (isset($address->getStreet()[1])) {
                $customerModel->line2 = $address->getStreet()[1];
            }

            $customerModel->city = $address->getCity();
            $customerModel->region = $address->getRegion()->getRegionCode();
            $customerModel->country = $address->getCountryId();
            $customerModel->postalCode = $address->getPostcode();
            $customerModel->phoneNumber = $address->getTelephone();
            $customerModel->faxNumber = $address->getFax();
            $customerModel->isBill = true;
        }

        return $customerModel;
    }
}
