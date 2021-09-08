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

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\Data\SDKTokenInterfaceFactory;
use ClassyLlama\AvaTax\Api\TokenInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;

/**
 * Class Token
 *
 * @package ClassyLlama\AvaTax\Model
 */
class Token extends Rest implements TokenInterface
{
    /**#@+
     * SDK Urls
     */
    const SDK_URL_DEV = 'https://sbx.certcapture.com/gencert2/js';
    const SDK_URL_PROD = 'https://app.certcapture.com/gencert2/js';
    /**#@-*/

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var SDKTokenInterfaceFactory
     */
    protected $tokenInterfaceFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Customer
     */
    protected $customerHelper;

    /**
     * @var \ClassyLlama\AvaTax\Model\Factory\CreateECommerceTokenInputModelFactory
     */
    protected $createECommerceTokenInputModelFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param SDKTokenInterfaceFactory $tokenInterfaceFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \ClassyLlama\AvaTax\Helper\Config $config
     * @param \ClassyLlama\AvaTax\Helper\Customer $customerHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool $clientPool
     * @param \ClassyLlama\AvaTax\Model\Factory\CreateECommerceTokenInputModelFactory $createECommerceTokenInputModelFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        SDKTokenInterfaceFactory $tokenInterfaceFactory,
        \Magento\Customer\Model\Session $customerSession,
        \ClassyLlama\AvaTax\Helper\Config $config,
        \ClassyLlama\AvaTax\Helper\Customer $customerHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool $clientPool,
        \ClassyLlama\AvaTax\Model\Factory\CreateECommerceTokenInputModelFactory $createECommerceTokenInputModelFactory
    )
    {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->customerHelper = $customerHelper;
        $this->createECommerceTokenInputModelFactory = $createECommerceTokenInputModelFactory;
    }

    /**
     * Function buildECommerceTokenInputModel
     *
     * @param $customerCode
     * @param null $scopeId
     * @param string $scopeType
     * @return \Avalara\CreateECommerceTokenInputModel
     */
    protected function buildECommerceTokenInputModel(
        $customerCode,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        /** @var \Avalara\CreateECommerceTokenInputModel $ECommerceTokenInputModel */
        $ECommerceTokenInputModel = $this->createECommerceTokenInputModelFactory->create();
        $ECommerceTokenInputModel->customerNumber = $customerCode;

        return $ECommerceTokenInputModel;
    }

    /**
     * @param $customerId
     *
     * @return \ClassyLlama\AvaTax\Api\Data\SDKTokenInterface|string
     */
    public function getTokenForCustomerId($customerId)
    {
        try {
            $client = $this->getClient();
            $client->withCatchExceptions(false);
            $customerCode = $this->customerHelper->getCustomerCodeByCustomerId($customerId);
            // Instantiates an Avalara class.
            $ECommerceTokenInputModel = $this->buildECommerceTokenInputModel($customerCode);
            $isProduction = $this->config->isProductionMode();

            $response = null;

            try {
                $response = $client->createECommerceToken(
                    $this->config->getCompanyId(),
                    $ECommerceTokenInputModel
                );
            } catch (\GuzzleHttp\Exception\RequestException $clientException) {
                // Validate the response; pass the customer id for context in case of an error.
                $this->handleException(
                    $clientException,
                    $this->dataObjectFactory->create(['customer_id' => $customerId])
                );
            }
            $result = $this->formatResult($response);

            if ($result === false) {
                return "Error parsing response from AvaTax: " . json_last_error_msg();
            }

            return $this->tokenInterfaceFactory->create(
                [
                    'data' => [
                        'token' => $result['token'],
                        'expires' => (new \DateTime($result['expiration_date']))->getTimestamp(),
                        'customer' => $customerCode,
                        'customer_id' => $customerId,
                        'sdk_url' => $isProduction ? self::SDK_URL_PROD : self::SDK_URL_DEV
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return \ClassyLlama\AvaTax\Api\Data\SDKTokenInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getToken()
    {
        return $this->getTokenForCustomerId($customerId = $this->customerSession->getCustomer()->getId());
    }
}
