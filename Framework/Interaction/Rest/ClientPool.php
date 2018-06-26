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

use Avalara\AvaTaxClient;
use Avalara\AvaTaxClientFactory;
use ClassyLlama\AvaTax\Helper\Config;

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
     * @param Config              $config
     * @param AvaTaxClientFactory $avaTaxClientFactory
     */
    public function __construct(
        Config $config,
        AvaTaxClientFactory $avaTaxClientFactory
    )
    {
        $this->config = $config;
        $this->avaTaxClientFactory = $avaTaxClientFactory;
    }

    protected function getClientCacheKey( $isProduction, $scopeType, $scopeId = null )
    {
        $cacheKey = $this->config->getMode( $isProduction );

        if ($scopeId !== null)
        {
            $cacheKey .= "-{$scopeId}";
        }

        return "{$cacheKey}-{$scopeType}";
    }

    /**
     * Get an AvaTax REST API client object
     *
     * @param null|string     $isProduction
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return AvaTaxClient
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        if ($isProduction === null)
        {
            $isProduction = $this->config->isProductionMode( $scopeId, $scopeType );
        }

        $cacheKey = $this->getClientCacheKey( $isProduction, $scopeType, $scopeId );

        if (!isset( $this->clients[ $cacheKey ] ))
        {
            /** @var AvaTaxClient $avaTaxClient */
            $avaTaxClient = $this->avaTaxClientFactory->create(
                [
                    'appName'     => $this->config->getApplicationName(),
                    'appVersion'  => $this->config->getApplicationVersion(),
                    'machineName' => $this->config->getApplicationDomain(),
                    'environment' => $isProduction ? self::API_MODE_PROD : self::API_MODE_DEV,
                ]
            );

            $accountNumber = $isProduction ? $this->config->getAccountNumber(
                $scopeId,
                $scopeType
            ) : $this->config->getDevelopmentAccountNumber( $scopeId, $scopeType );
            $licenseKey = $isProduction ? $this->config->getLicenseKey(
                $scopeId,
                $scopeType
            ) : $this->config->getDevelopmentLicenseKey( $scopeId, $scopeType );

            $avaTaxClient->withSecurity( $accountNumber, $licenseKey );

            $this->clients[ $cacheKey ] = $avaTaxClient;
        }

        return $this->clients[ $cacheKey ];
    }
}