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
use \Magento\Framework\DataObjectFactory;

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

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /** @var array */
    protected $clients = [];

    /**
     * @param Config $config
     * @param AvaTaxClientFactory $avaTaxClientFactory
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Config $config,
        AvaTaxClientFactory $avaTaxClientFactory,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->config = $config;
        $this->avaTaxClientFactory = $avaTaxClientFactory;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
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
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    public function ping($mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $client = $this->getClient($mode, $scopeId, $scopeType);
        $result = $client->ping();

        $this->validateResult($result);

        return $result->authenticated;
    }

    /**
     * Validate a response from the AvaTax library client
     * Response is an error message string if an error occurred
     *
     * @param string|\Avalara\PingResultModel $result
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateResult($result)
    {
        if (!is_object($result)) {
            if (is_string($result)) {
                $this->logger->error(__('AvaTax connection error: %1', $result));
            } else {
                $this->logger->error(__('Response from AvaTax was in invalid format'));
            }
            // TODO: Better exception class
            throw new \Magento\Framework\Exception\LocalizedException(__('AvaTax connection error'));
        }

        /**
         * This really should never happen, because the response should come back with a response code that
         * results in the Guzzle middleware throwing an exception, which Avalara catches and turns into a flat string result
         */
        if (isset($result->error)) {
            if (is_object($result->error) && isset($result->error->message)) {
                $this->logger->error(__('AvaTax connection error: %1', $result->error->message));
            } else {
                $this->logger->error(__('Response from AvaTax indicated non-specific error'));
            }
            // TODO: Better exception class
            throw new \Magento\Framework\Exception\LocalizedException(__('AvaTax connection error'));
        }
    }

    /**
     * Convert a simple object to a data object
     *
     * @param mixed $value
     * @return mixed
     */
    protected function formatResult($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = $this->formatResult($subValue);
            }
        } elseif (is_object($value)) {
            $valueObj = $this->dataObjectFactory->create();
            foreach ($value as $key => $subValue) {
                $methodName = 'set' . ucfirst($key);
                call_user_func([$valueObj, $methodName], $this->formatResult($subValue));
            }

            $value = $valueObj;
        }

        return $value;
    }
}