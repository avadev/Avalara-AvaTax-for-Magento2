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

class Token implements TokenInterface
{
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\DeploymentConfig    $deploymentConfig
     * @param SDKTokenInterfaceFactory                   $tokenInterfaceFactory
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \ClassyLlama\AvaTax\Helper\Config          $config
     * @param \ClassyLlama\AvaTax\Helper\Customer        $customerHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        SDKTokenInterfaceFactory $tokenInterfaceFactory,
        \Magento\Customer\Model\Session $customerSession,
        \ClassyLlama\AvaTax\Helper\Config $config,
        \ClassyLlama\AvaTax\Helper\Customer $customerHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param $customerId
     *
     * @return \ClassyLlama\AvaTax\Api\Data\SDKTokenInterface|string
     */
    public function getTokenForCustomerId($customerId)
    {
        try {
            $certCaptureConfig = $this->deploymentConfig->get('cert-capture');

            if (!isset(
                $certCaptureConfig['auth']['username'], $certCaptureConfig['auth']['password'], $certCaptureConfig['sdk-url'], $certCaptureConfig['client-id']
            )) {
                return "Invalid Deployment Configuration";
            }

            $auth = base64_encode("{$certCaptureConfig['auth']['username']}:{$certCaptureConfig['auth']['password']}");
            $customerCode = $this->customerHelper->getCustomerCodeByCustomerId($customerId);

            // use key 'http' even if you send the request to https://...
            $options = [
                'http' => [
                    'header' => [
                        'Content-type: application/json',
                        "x-client-id: {$certCaptureConfig['client-id']}",
                        "x-customer-number: {$customerCode}",
                        "Authorization: Basic $auth"
                    ],
                    'method' => 'POST',
                    'content' => http_build_query([])
                ]
            ];

            $context = stream_context_create($options);

            $result = json_decode(file_get_contents($certCaptureConfig['url'], false, $context), true);

            if ($result === false) {
                return "Error parsing response from AvaTax: " . json_last_error_msg();
            }

            return $this->tokenInterfaceFactory->create(
                [
                    'data' => [
                        'token' => $result['response']['token'],
                        'expires' => (new \DateTime($result['response']['expires_at']))->getTimestamp(),
                        'customer' => $customerCode,
                        'customer_id' => $customerId,
                        'client_id' => $certCaptureConfig['client-id'],
                        'sdk_url' => $certCaptureConfig['sdk-url']
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
