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

use ClassyLlama\AvaTax\Helper\Config;
use Avalara\AvaTaxClient;
use Avalara\AvaTaxClientFactory;

class ClientPool
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

    /** @var array */
    protected $clients = [];

    /**
     * @param Config $config
     * @param AvaTaxClientFactory $avaTaxClientFactory
     */
    public function __construct(
        Config $config,
        AvaTaxClientFactory $avaTaxClientFactory
    ) {
        $this->config = $config;
        $this->avaTaxClientFactory = $avaTaxClientFactory;
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

        $cacheKey = $mode;
        if (!is_null($scopeId)) {
            $cacheKey .= '-' . $scopeId;
        }
        $cacheKey .= '-' . $scopeType;

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
}