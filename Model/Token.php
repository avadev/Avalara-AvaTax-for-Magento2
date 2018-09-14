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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\DeploymentConfig    $deploymentConfig
     * @param SDKTokenInterfaceFactory                   $tokenInterfaceFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        SDKTokenInterfaceFactory $tokenInterfaceFactory
    )
    {
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
    }

    /**
     * @return \ClassyLlama\AvaTax\Api\Data\SDKTokenInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getToken()
    {
        try {
            $certCaptureConfig = $this->deploymentConfig->get('cert-capture');

            if (!isset(
                $certCaptureConfig['auth']['username'], $certCaptureConfig['auth']['password'], $certCaptureConfig['client-id'], $certCaptureConfig['customer-number'], $certCaptureConfig['sdk-url']
            )) {
                return "Invalid Deployment Configuration";
            }

            $auth = base64_encode("{$certCaptureConfig['auth']['username']}:{$certCaptureConfig['auth']['password']}");

            // use key 'http' even if you send the request to https://...
            $options = [
                'http' => [
                    'header' => [
                        'Content-type: application/json',
                        "x-client-id: {$certCaptureConfig['client-id']}",
                        "x-customer-number: {$certCaptureConfig['customer-number']}",
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
                        'customer' => $certCaptureConfig['customer-number'],
                        'client_id' => $certCaptureConfig['client-id'],
                        'sdk_url' => $certCaptureConfig['sdk-url']
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
