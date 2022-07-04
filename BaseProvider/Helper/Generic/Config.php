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
namespace ClassyLlama\AvaTax\BaseProvider\Helper\Generic;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Avalara GenericLogger Config helper
 */
class Config extends AbstractHelper
{    
    const API_LOG_TYPE_PERFORMANCE = 'performance';
    const API_LOG_TYPE_DEBUG = 'debug';
    const API_LOG_TYPE_CONFIG = 'config';
    
    const API_LOG_LEVEL_ERROR = 'error';
    const API_LOG_LEVEL_EXCEPTION = 'exception';
    const API_LOG_LEVEL_INFO = 'info';

    /**
     * Api Log Types
     */
    const API_LOG_TYPE = [
        self::API_LOG_TYPE_PERFORMANCE => 'Performance', 
        self::API_LOG_TYPE_DEBUG => 'Debug', 
        self::API_LOG_TYPE_CONFIG => 'ConfigAudit'
    ];

    /**
     * Api Log Levels
     */
    const API_LOG_LEVEL = [
        self::API_LOG_LEVEL_ERROR => 'Error', 
        self::API_LOG_LEVEL_EXCEPTION => 'Exception', 
        self::API_LOG_LEVEL_INFO => 'Informational'
    ];

    /**
     * Sandbox API URL for LOGGER
     */
    const ENV_LOGGER_SANDBOX_BASE_URL = 'https://ceplogger.sbx.avalara.com';

    /**
     * Production API URL for LOGGER
     */
    const ENV_LOGGER_PRODUCTION_BASE_URL = 'https://ceplogger.avalara.com';

    /**
     * Endpoint for logger
     */
    const API_LOGGER_ENDPOINT = '/api/logger/';

    /**
     * String value of API mode
     */
    const API_MODE_PRODUCTION = 'production';

    /**
     * String value of API mode
     */
    const API_MODE_SANDBOX = 'sandbox';

    /**
     * ERP details of application
     */
    const ERP_DETAILS = "MAGENTO";

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ProductMetadataInterface
     */
    protected $mageMetadata = null;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param TimezoneInterface $timeZone
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timeZone,
        UrlInterface $urlInterface,
        ProductMetadataInterface $mageMetadata
    ) {
        parent::__construct($context);
        $this->timeZone = $timeZone;
        $this->urlInterface = $urlInterface;
        $this->mageMetadata = $mageMetadata;
    }
    
    /**
     * Returns current store time zone object
     *
     * @return TimezoneInterface
     */
    public function getTimeZoneObject()
    {
        return $this->timeZone;
    }

    /**
     * Generate Avalara Application Name from a combination of Magento version number and Avalara module name
     * Format: Magento 2.x Community - Avalara
     * Limited to 50 characters to comply with API requirements
     *
     * @return string
     */
    public function getApplicationName()
    {
        return substr($this->mageMetadata->getName(), 0, 7) . ' ' . // "Magento" - 8 chars
            substr(
                $this->mageMetadata->getVersion(),
                0,
                14
            ) . ' ' . // 2.x & " " - 50 - 8 - 13 - 14 = 15 chars
            substr(
                $this->mageMetadata->getEdition(),
                0,
                10
            ) . ' - ' . // "Community - "|"Enterprise - " - 13 chars
            'Avalara';
    }

    /**
     * Get the base URL minus protocol and trailing slash, for use as machine name in API requests
     *
     * @return string
     */
    public function getMachineName()
    {
        $domain = $this->urlInterface->getBaseUrl();
        $domain = preg_replace('#^https?://#', '', $domain);
        return preg_replace('#/$#', '', $domain);
    }

    /**
     * Prepare Basic Auth Token 
     *
     * @param string $accountSecret
     * @param string $accountSecret
     * @return string
     */
    public function prepareBasicAuthToken($accountNumber, $accountSecret)
    {
        return base64_encode($accountNumber . ":" . $accountSecret);
    }

    /**
     * Validate Record 
     *
     * @param $record array
     * @return boolean
     */
    public function validateRecord(array $record)
    {
        $exception = [];

        if (!isset($record['config'])) {
            $exception[] = "Configuration parameters are missing for API Logging.";
        } else {
            if (!isset($record['config']['account_number'])) {
                $exception[] = "account_number parameters is missing for API Logging.";
            }
            if (!(isset($record['config']['auth']) && $record['config']['auth'] == "Bearer") && !isset($record['config']['account_secret'])) {
                $exception[] = "account_secret parameters is missing for API Logging.";
            }
            if (!isset($record['config']['connector_id'])) {
                $exception[] = "connector_id parameters is missing for API Logging.";
            }
            if (!isset($record['config']['client_string'])) {
                $exception[] = "client_string parameters is missing for API Logging.";
            }
        }

        if (empty($exception)) {
            return true;
        }

        return false;
    }
}
