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

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use ClassyLlama\AvaTax\BaseProvider\Logger\GenericLogger;
use ClassyLlama\AvaTax\Framework\AppInterface;

class ApiLog extends AbstractHelper
{
    const CONNECTOR_ID = 'a0n5a00000ZmXLNAA3'; //'a0o5a000007TuRvAAK'; # @TODO Uncomment this after S3 whitelist

    const CONNECTOR_STRING = AppInterface::CONNECTOR_STRING;

    const CONNECTOR_NAME = AppInterface::APP_NAME;

    const HTML_ESCAPE_PATTERN = '/<(.*) ?.*>(.*)<\/(.*)>/';                         

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GenericLogger
     */
    protected $genericLogger;

    /**
     * @param Config  $config
     * @param Context $context
     * @param GenericLogger $genericLogger
     */
    public function __construct(Config $config, Context $context, GenericLogger $genericLogger)
    {
        parent::__construct($context);

        $this->config = $config;
        $this->genericLogger = $genericLogger;
    }

    /**
     * API Logging
     *
     * @param string $message
     * @param array $context
     * @param $scopeId|Null
     * @param $scopeType|Null
     * @return void
     */
    public function apiLog(string $message, array $context = [], $scopeId = null, $scopeType = null)
    {
        if (strlen($message) > 0) {

            $isProduction = $this->config->isProductionMode($scopeId, $scopeType);
            $accountNumber = $this->config->getAccountNumber($scopeId, $scopeType, $isProduction);
            $accountSecret = $this->config->getLicenseKey($scopeId, $scopeType, $isProduction);
            $connectorId = self::CONNECTOR_ID;
            $clientString = self::CONNECTOR_STRING;
            $mode = $isProduction ? \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_MODE_PRODUCTION : \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_MODE_SANDBOX;
            $connectorName = self::CONNECTOR_NAME;
            $connectorVersion = \ClassyLlama\AvaTax\Framework\AppInterface::APP_VERSION;
            $source = isset($context['config']['source']) ? $context['config']['source'] : 'MagentoPage';
            $operation = isset($context['config']['operation']) ? $context['config']['operation'] : 'MagentoOperation';
            $logType = isset($context['config']['log_type']) ? $context['config']['log_type'] : \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_DEBUG;
            $logLevel = isset($context['config']['log_level']) ? $context['config']['log_level'] : \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
            $functionName = isset($context['config']['function_name']) ? $context['config']['function_name'] : __METHOD__;
            
            $params = [
                'config' => [
                    'account_number' => $accountNumber,
                    'account_secret' => $accountSecret,
                    'connector_id' => $connectorId,
                    'client_string' => $clientString,
                    'mode' => $mode,
                    'connector_name' => $connectorName,
                    'connector_version' => $connectorVersion,
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName
                ]
            ];
            #echo   $message;
            #echo "<pre>";
            #print_r($params);die;
            $this->genericLogger->apiLog($message, [$params]);
        }
    }

    /**
     * configSaveLog API Logging
     *
     * @param $scopeId
     * @param $scopeType
     * @return void
     */
    public function configSaveLog($scopeId, $scopeType)
    {
        $message = "";
        $source = 'ConfigurationPage';
        $operation = 'ConfigSave';
        $logType = \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_CONFIG;
        $logLevel = \ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
        $functionName = __METHOD__;
        $context = [
            'config' => [
                'source' => $source,
                'operation' => $operation,
                'log_type' => $logType,
                'log_level' => $logLevel,
                'function_name' => $functionName
            ]
        ];
        $data = $this->getConfigData($scopeId, $scopeType);
        $message = json_encode($data);
        $this->apiLog($message, $context, $scopeId, $scopeType);
    }

    /**
     * getConfigData function
     *
     * @param $scopeId
     * @param $scopeType
     * @return array
     */
    private function getConfigData($store)
    {
        $configPaths = [
            "AvaTax - General" => "tax/avatax",  
            "AvaTax - Customs" => "tax/avatax_customs",
            "AvaTax - Document Management" => "tax/avatax_document_management", 
            "AvaTax - Certificate Capture Management" => "tax/avatax_certificate_capture",
            "AvaTax - Advanced" => "tax/avatax_advanced"
        ];

        foreach ($configPaths as $title=>$path) {
            $groupData = $this->config->getConfigData($path, $store);
            $data[$title] = $this->escapeAvaTaxData($groupData);
        }

        if (isset($data["AvaTax - General"]["production_license_key"])) {
            unset($data["AvaTax - General"]["production_license_key"]);
        }

        if (isset($data["AvaTax - General"]["development_license_key"])) {
            unset($data["AvaTax - General"]["development_license_key"]);
        }
        
        return $data;
    }

    /**
     * escapeAvaTaxData function
     *
     * @param array $data
     * @return array
     */
    public function escapeAvaTaxData(array $data)
    {
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $key=>&$value) {
            if (is_array($value)) {
                $value = $this->escapeAvaTaxData($value);
            } else {
                /* To Exclude html value from log */
                if (preg_match(self::HTML_ESCAPE_PATTERN, $value)) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
}