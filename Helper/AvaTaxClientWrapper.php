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

use Psr\Log\LoggerInterface;

class AvaTaxClientWrapper extends \Avalara\AvaTaxClient
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Config          $config
     * @param LoggerInterface $logger
     * @param string          $appName
     * @param string          $appVersion
     * @param string          $machineName
     * @param string          $environment
     * @param array           $guzzleParams
     *
     * @throws \Exception
     */
    public function __construct(
        \ClassyLlama\AvaTax\Helper\Config $config,
        LoggerInterface $logger,
        $appName,
        $appVersion,
        $machineName = "",
        $environment,
        array $guzzleParams = []
    )
    {
        parent::__construct($appName, $appVersion, $machineName, $environment, $guzzleParams);

        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeRequest($verb, $apiUrl, $guzzleParams)
    {
        $response = parent::executeRequest($verb, $apiUrl, $guzzleParams);

        // The body is already encoded as JSON, we need to decode it first so we don't double-encode it
        if (is_string($guzzleParams['body'])) {
            $guzzleParams['body'] = json_decode($guzzleParams['body']);
        }

        $this->logger->debug(
            "Loaded REST result from $apiUrl",
            [
                'request' => json_encode(
                    [
                        'url' => $apiUrl,
                        'method' => $verb,
                        'parameters' => $guzzleParams
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                ),
                'result' => json_encode(json_decode((string)$response->getBody()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            ]
        );

        return $response;
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

    /**
     * {@inheritDoc}
     *
     * This method needs overridden so that we can specify a different accept header to ensure that the rest call
     * doesn't attempt to parse the response as json
     */
    public function downloadCertificateImage($companyId, $id, $page, $type)
    {
        $path = "/api/v2/companies/{$companyId}/certificates/{$id}/attachment";
        $guzzleParams = [
            'query' => ['$page' => $page, '$type' => $type],
            'body' => null,
            'headers' => [
                'Accept' => '*/*'
            ]
        ];

        return $this->restCall($path, 'GET', $guzzleParams);
    }
}
