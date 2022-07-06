<?php
/*
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
namespace ClassyLlama\AvaTax\BaseProvider\Logger\Handler\Generic;

use ClassyLlama\AvaTax\BaseProvider\Logger\Handler\BaseAbstractHandler;
use ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger;
use ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config;
use Magento\Framework\Webapi\Rest\Request;
use ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer as QueueProducer;

/**
 * @codeCoverageIgnore
 */
class ApiHandler extends BaseAbstractHandler
{
    /**
     * @var QueueProducer
     */
    protected $queueProducer;

    /**
     * @var Config
     */
    protected $loggerConfig;

    public function __construct(
        Config $config,
        QueueProducer $queueProducer
    ) {
        $this->queueProducer = $queueProducer;
        $this->loggerConfig = $config;
        parent::__construct(GenericLogger::API_LOG, true);
    }

    /**
     * @param $record array
     * @return array
     */
    private function getApiConfig(array $record)
    {
        $apiConfig = [];

        $context = isset($record['context']) ? $record['context'] : [];
        
        if (empty($context)) {
            return $apiConfig;
        }

        foreach ($context as $key=>$data) {
            if (!is_array($data)) {
                continue;
            }
            if (empty($data)) {
                continue;
            }
            if (isset($data['config'])) {
                $apiConfig = $data;
                break;
            }
        }

        return $apiConfig;
    }

    /**
     * Send log to the third party using API
     *
     * @param $record array
     * @return void
     */
    public function write(array $record) : void
    {
        $apiConfig = $this->getApiConfig($record);
        $isValid = $this->loggerConfig->validateRecord($apiConfig);

        if ($isValid) {

            $accountNumber = $apiConfig['config']['account_number'];
            $accountSecret = isset($apiConfig['config']['account_secret']) ? $apiConfig['config']['account_secret'] : '';
            $connectorId = $apiConfig['config']['connector_id'];
            $connectorString = $apiConfig['config']['client_string'];

            $currentMode = isset($apiConfig['config']['mode']) ? $apiConfig['config']['mode'] : Config::API_MODE_SANDBOX;
            $connectorName = isset($apiConfig['config']['connector_name']) ? $apiConfig['config']['connector_name'] : 'Magento Connectors';
            $connectorVersion = isset($apiConfig['config']['connector_version']) ? $apiConfig['config']['connector_version'] : '1.0.0';
            $source = isset($apiConfig['config']['source']) ? $apiConfig['config']['source'] : 'ConfigurationPage';
            $operation = isset($apiConfig['config']['operation']) ? $apiConfig['config']['operation'] : 'Test Connection';
            $logType = isset($apiConfig['config']['log_type']) ? $apiConfig['config']['log_type'] : Config::API_LOG_TYPE_CONFIG;
            $logType = Config::API_LOG_TYPE[$logType];
            $logLevel = isset($apiConfig['config']['log_level']) ? $apiConfig['config']['log_level'] : Config::API_LOG_LEVEL_INFO; 
            $logLevel = Config::API_LOG_LEVEL[$logLevel];
            $functionName = isset($apiConfig['config']['function_name']) ? $apiConfig['config']['function_name'] : __METHOD__ ; 
            $accessToken = isset($apiConfig['config']['access_token']) ? $apiConfig['config']['access_token'] : $this->loggerConfig->prepareBasicAuthToken($accountNumber, $accountSecret);
            $endPointBaseUrl = Config::ENV_LOGGER_PRODUCTION_BASE_URL;
            $endPointUri = isset($apiConfig['config']['uri']) ? $apiConfig['config']['uri'] : Config::API_LOGGER_ENDPOINT.$connectorId;        
            $authType = isset($apiConfig['config']['auth']) ? $apiConfig['config']['auth'] : 'Basic';
            $returnType = isset($apiConfig['config']['return_type']) ? $apiConfig['config']['return_type'] : 'array';
            $machineName = isset($apiConfig['config']['machine_name']) ? $apiConfig['config']['machine_name'] : $this->loggerConfig->getMachineName();
            $headers = isset($apiConfig['config']['extra_headers']) ? $apiConfig['config']['extra_headers'] : [];
            $extraParams = isset($apiConfig['config']['extra_params']) ? $apiConfig['config']['extra_params'] : [];
            $requestMethod = isset($apiConfig['config']['request_method']) ? $apiConfig['config']['request_method'] : Request::HTTP_METHOD_POST;

            if ($currentMode == Config::API_MODE_SANDBOX) {
                $endPointBaseUrl = Config::ENV_LOGGER_SANDBOX_BASE_URL;
            }
            if (isset($apiConfig['config']['base_url'])) {
                $endPointBaseUrl = $apiConfig['config']['base_url'];
            }

            $params = [
                "Source" => $source,
                "Operation" => $operation,
                "Message" => isset($record['message']) ? $record['message'] : '',
                "LogType" => $logType,
                "LogLevel" => $logLevel,
                "FunctionName" => $functionName
            ];

            $params["CallerAccuNum"] = $accountNumber;
            $params["AvaTaxEnvironment"] = ucwords($currentMode);
            $params["ERPDetails"] = Config::ERP_DETAILS;
            $params["ConnectorName"] = $connectorName;
            $params["ConnectorVersion"] = $connectorVersion;
            $params["ClientString"] = $connectorString;
            
            if (!empty($extraParams)) {
                $params = array_merge($params, $extraParams);
            }

            $params = [
                'query' => [],
                'body' => json_encode($params)
            ];

            if (!empty($headers)) {
                $params = array_merge($params, $headers);
            }

            $params['endpoint'] = $endPointUri;

            $queueClient  = \ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\ApiLogConsumer::CLIENT;
            $payload = [
                "setClient" => [
                    $endPointBaseUrl,
                    $accessToken,
                    $returnType,
                    $currentMode,
                    $connectorName,
                    $connectorVersion,
                    $machineName,
                    $authType
                ],
                "restCall" => [
                    $params,
                    $requestMethod
                ]
            ];
            $payload = json_encode($payload);
            $this->queueProducer->addJob($queueClient, $payload);
        }
    }
}
