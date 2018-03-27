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

namespace ClassyLlama\AvaTax\Framework\Interaction;

use ClassyLlama\AvaTax\Helper\Config;
use Avalara\AvaTaxClient;
use Avalara\AvaTaxClientFactory;
use Psr\Log\LoggerInterface;

class Rest
{
    const API_MODE_PROD = 'production';

    const API_MODE_DEV = 'sandbox';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AvaTaxClientFactory
     */
    protected $avaTaxClientFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /** @var array */
    protected $clients = [];

    /**
     * @param Config $config
     * @param AvaTaxClientFactory $avaTaxClientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        AvaTaxClientFactory $avaTaxClientFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->avaTaxClientFactory = $avaTaxClientFactory;
        $this->logger = $logger;
    }

    /**
     * Get an AvaTax REST API client object
     *
     * @param null|string $mode
     * @param null|string|int $scopeId
     * @param string $scopeType
     * @return AvaTaxClient
     * @throws \InvalidArgumentException
     */
    public function getClient($mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        if (is_null($mode)) {
            $mode = $this->config->getLiveMode($scopeId, $scopeType) ? Config::API_PROFILE_NAME_PROD : Config::API_PROFILE_NAME_DEV;
        }

        $cacheKey = $mode . '-' . $scopeId . '-' . $scopeType;

        if (!isset($this->clients[$cacheKey])) {
            /** @var AvaTaxClient $avaTaxClient */
            $avaTaxClient = $this->avaTaxClientFactory->create([
                'appName' => $this->config->getApplicationName(),
                'appVersion' => $this->config->getApplicationVersion(),
                'machineName' => $this->config->getApplicationDomain(),
                'environment' => ($mode == Config::API_PROFILE_NAME_PROD) ? self::API_MODE_PROD : self::API_MODE_DEV,
            ]);

            $accountNumber = ($mode == Config::API_PROFILE_NAME_PROD) ? $this->config->getAccountNumber($scopeId, $scopeType) : $this->config->getDevelopmentAccountNumber($scopeId, $scopeType);
            $licenseKey = ($mode == Config::API_PROFILE_NAME_PROD) ? $this->config->getLicenseKey($scopeId, $scopeType) : $this->config->getDevelopmentLicenseKey($scopeId, $scopeType);

            $avaTaxClient->withSecurity($accountNumber, $licenseKey);

            $this->clients[$cacheKey] = $avaTaxClient;
        }

        return $this->clients[$cacheKey];
    }

    /**
     * Ping AvaTax REST service to verify connection/authentication
     *
     * @param null|string $mode
     * @param null|string|int $scopeId
     * @param string $scopeType
     * @return \Avalara\PingResultModel
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    public function ping($mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $client = $this->getClient($mode, $scopeId, $scopeType);
        $result = $client->ping();

        $this->validateResponse($result);

        if (!$result->authenticated) {
            // TODO: Better exception class
            throw new \Magento\Framework\Exception\LocalizedException(__('AvaTax authentication failed'));
        }

        return $result;
    }

    /**
     * Validate a response from the AvaTax library client
     * Response is an error message string if an error occurred
     *
     * @param string|\Avalara\PingResultModel $result
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateResponse($result)
    {
        if (!is_object($result)) {
            $message = __('AvaTax connection error');
            if (is_string($result)) {
                $message = __('AvaTax authentication failed');
                $this->logger->error(__('AvaTax connection error: %1', $result));
            }
            // TODO: Better exception class
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }
    }
}