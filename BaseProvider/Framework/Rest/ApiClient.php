<?php
/*
 *
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace ClassyLlama\AvaTax\BaseProvider\Framework\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class ApiClient
 */
class ApiClient
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var bool The setting for whether the client should catch exceptions
     */
    protected $catchExceptions;

    /**
     * @var Array  Additional headers
     */
    protected $additionalParams;

    /**
     * @var String
     */
    protected $endPointBaseUrl;
    protected $accessToken;
    protected $responseType;
    protected $mode;
    protected $appName;
    protected $appVersion;
    protected $machineName;
    protected $authType;

    /**
     * GitApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->catchExceptions = true;
    }

    /**
     * Set ApiClient
     *
     * @param string $endPointBaseUrl
     * @param string $accessToken
     * @param string $responseType
     * @param string $mode
     * @param string $appName
     * @param string $appVersion
     * @param string $machineName
     * @param string $authType
     * @return ApiClient
     */
    public function setClient(
        string $endPointBaseUrl,
        $accessToken = '',
        $responseType = 'array',
        $mode = 'sandbox',
        $appName = '',
        $appVersion = '1.0',
        $machineName = 'localhost',
        $authType = 'Bearer'
    ) {
        $this->endPointBaseUrl = $endPointBaseUrl;
        $this->accessToken = $accessToken;
        $this->responseType = $responseType;
        $this->mode = $mode;
        $this->appName = $appName;
        $this->appVersion = $appVersion;
        $this->machineName = $machineName;
        $this->authType = $authType;
        return $this;
    }

    /**
     * RestCall with provided params
     *
     * @param array $params
     * @param string $requestMethod
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function restCall(
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET,
        $getArray = false
    ) {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->endPointBaseUrl
        ]]);

        if ($this->machineName == null) {
            $this->machineName = "";
        }

        $params = $this->addHeaders($params);
        // pass additional headers
        if ($this->additionalParams) {
            foreach ($this->additionalParams as $key => $value) {
                $params['headers'][$key] = $value;
            }
        }
        
        $params['timeout'] = isset($params['timeout']) ? (int) $params['timeout'] : 1200;

        try {
            $endPointUri = '';
            if (isset($params['endpoint']) && ($params['endpoint'] != '')) {
                $endPointUri = $params['endpoint'];
            }
            $response = $client->request(
                $requestMethod,
                $endPointUri,
                $params
            );
            $body = (string) $response->getBody();
            $JsonBody = json_decode($body, $getArray);
            if (($this->responseType == 'array') && (!is_null($JsonBody))) {
                return $JsonBody;
            } else {
                return $body;
            }
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }
            return $e->getMessage();
        }
    }

    /**
     * add headers
     *
     * @param  array          $params
     * @return array
     */
    protected function addHeaders($params)
    {
        if (!isset($params['headers']['Accept'])) {
            $params['headers']['Accept'] = 'application/json';
        }
        if (!isset($params['headers']['Content-Type'])) {
            $params['headers']['Content-Type'] = 'application/json';
        }
        if (!isset($params['headers']['Authorization'])) {
            $params['headers']['Authorization'] = $this->authType . ' ';    
            if ($this->accessToken != '') {
                $params['headers']['Authorization'] = $params['headers']['Authorization'] . $this->accessToken;            
            }			
        }
        
        if (!isset($params['headers']['X-Avalara-Client']) && $this->authType == 'Bearer') {
            $params['headers']['X-Avalara-Client'] = "{$this->appName}; {$this->appVersion}; PhpRestClient; 20.12.1; {$this->machineName}";   
        }
        return $params;
    }

    /**
     * Configure this client to use the specified username/password security settings
     *
     * @param  string          $username   The username for your AvaTax user account
     * @param  string          $password   The password for your AvaTax user account
     * @return ApiClient
     */
    public function withSecurity($username, $password)
    {
        $this->auth = [$username, $password];
        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
     * @return ApiClient
     */
    public function withLicenseKey($accountId, $licenseKey)
    {
        $this->auth = [$accountId, $licenseKey];
        return $this;
    }

    /**
     * Set additional headers
     *
     * @param  array $params
     * @return ApiClient
     */
    public function addtionalHeaders($params)
    {
        $this->additionalParams = $params;
        return $this;
    }

    /**
     * Configure this client to use bearer token
     *
     * @param  string          $bearerToken     The private bearer token for your AvaTax account
     * @return ApiClient
     */
    public function withBearerToken($bearerToken)
    {
        $this->auth = [$bearerToken];
        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
     * @return ApiClient
     */
    public function withBasicToken($accountId, $licenseKey)
    {
        // Third element '3' added to the array to support multiple authentication methods
        $this->auth = ['type', base64_encode($accountId . ":" . $licenseKey), '3'];
        return $this;
    }

    /**
     * Configure the client to either catch web request exceptions and return a message or throw the exception
     *
     * @param bool $catchExceptions
     * @return ApiClient
     */
    public function withCatchExceptions($catchExceptions = true)
    {
        $this->catchExceptions = $catchExceptions;
        return $this;
    }

    /**
     * Return the client object, for extended class(es) to retrive the client object
     *
     * @return ApiClient
     */
    public function getClient()
    {
        return $this;
    }
}
