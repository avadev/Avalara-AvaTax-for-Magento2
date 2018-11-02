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

namespace ClassyLlama\AvaTax\Helper;

class AvaTaxClientWrapper extends \Avalara\AvaTaxClient
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     * @param string $appName
     * @param string $appVersion
     * @param string $machineName
     * @param string $environment
     * @param array  $guzzleParams
     *
     * @throws \Exception
     */
    public function __construct(
        \ClassyLlama\AvaTax\Helper\Config $config,
        $appName,
        $appVersion,
        $machineName = "",
        $environment,
        array $guzzleParams = []
    )
    {
        parent::__construct($appName, $appVersion, $machineName, $environment, $guzzleParams);

        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    protected function restCall($apiUrl, $verb, $guzzleParams)
    {
        if (!\is_array($guzzleParams)) {
            $guzzleParams = [];
        }

        if (!isset($guzzleParams['timeout'])) {
            $guzzleParams['timeout'] = $this->config->getAvaTaxApiTimeout();
        }

        // Warning: This causes the value to revert to the default "forever" timeout in guzzle
        if (\is_nan($guzzleParams['timeout'])) {
            $guzzleParams['timeout'] = 0;
        }

        return parent::restCall($apiUrl, $verb, $guzzleParams);
    }
}
