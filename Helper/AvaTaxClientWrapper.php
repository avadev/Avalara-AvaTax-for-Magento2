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
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use ClassyLlama\AvaTax\Helper\Config as ConfigHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;

/**
 * Class AvaTaxClientWrapper
 * @package ClassyLlama\AvaTax\Helper
 */
class AvaTaxClientWrapper extends \Avalara\AvaTaxClient
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * AvaTaxClientWrapper constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param Config $config
     * @param LoggerInterface $logger
     * @param string $appName
     * @param string $appVersion
     * @param string $machineName
     * @param string $environment
     * @param array $guzzleParams
     * @throws \Exception
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ConfigHelper $config,
        LoggerInterface $logger,
        string $appName,
        string $appVersion,
        string $machineName = "",
        string $environment = '',
        array $guzzleParams = []
    ) {
        parent::__construct($appName, $appVersion, $machineName, $environment, $guzzleParams);
        $this->config = $config;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
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
    protected function restCall($apiUrl, $verb, $guzzleParams, $apiversion = '', $headerParams = null)
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

        return $this->tryToMakeRestCall($path, 'GET', $guzzleParams);
    }

    /**
     * Try to load a certificate PDF file
     *
     * @param string|null $apiUrl
     * @param string|null $method
     * @param array $guzzleParams
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    private function tryToMakeRestCall(string $apiUrl = '', string $method = 'GET', array $guzzleParams = [])
    {
        if (!empty($apiUrl)) {
            // this causes the value to revert to the default "forever" timeout in guzzle
            $guzzleParams['timeout'] = !empty($timeout = (int)$this->config->getAvaTaxApiTimeout()) ? $timeout : 0;
            // set authentication on the parameters
            if (count($this->auth) == 2) {
                if (!isset($guzzleParams['auth'])) {
                    $guzzleParams['auth'] = $this->auth;
                }
                $guzzleParams['headers'] = [
                    'Accept' => 'application/json',
                    'X-Avalara-Client' => "{$this->appName}; {$this->appVersion}; PhpRestClient; 18.12.0; {$this->machineName}"
                ];
            } else {
                $guzzleParams['headers'] = [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->auth[0] ?? '',
                    'X-Avalara-Client' => "{$this->appName}; {$this->appVersion}; PhpRestClient; 18.12.0; {$this->machineName}"
                ];
            }
            try {
                /** @var GuzzleHttpResponse $response */
                $response = $this->client->request($method, $apiUrl, $guzzleParams);
                if (200 === (int)$response->getStatusCode()) {
                    $this->logRequests(
                        'Certificate PDF file request',
                        $apiUrl,
                        $method,
                        $guzzleParams,
                        $response
                    );
                    return !empty($contents = (string)$response->getBody()->getContents()) ? $contents : null;
                }
                // case, when the response code from AvaTax is not equal to 200
                $this->logRequests(
                    'Certificate PDF file request, status code is not 200',
                    $apiUrl,
                    $method,
                    $guzzleParams,
                    $response
                );
                return null;
            } catch (\Throwable $exception) {
                if (false === (bool)$this->catchExceptions) {
                    throw $exception;
                }
                $this->logRequests(
                    $exception->getMessage(),
                    $apiUrl,
                    $method,
                    $guzzleParams
                );
                return null;
            }
        }
        return null;
    }

    /**
     * Log requests|responses data
     *
     * @param string|null $context
     * @param string|null $apiUrl
     * @param string|null $method
     * @param array $guzzleParams
     * @param GuzzleHttpResponse|null $response
     */
    private function logRequests(
        string $context = '',
        string $apiUrl = '',
        string $method = '',
        array $guzzleParams = [],
        GuzzleHttpResponse $response = null
    ) {
        /** @var DataObject $data */
        $data = $this->dataObjectFactory->create();
        $data->setData('context', $context);
        $data->setData('request', [
            'api_url' => $apiUrl,
            'http_method' => $method,
            'guzzle_parameters' => $guzzleParams
        ]);
        $data->setData('response', [
            'status_code' => null !== $response ? $response->getStatusCode() : ''
        ]);
        $data->setData('additional_data', ['class' => self::class]);
        $this->logger->debug(print_r($data, true));
    }
}
