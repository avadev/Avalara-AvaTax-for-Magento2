<?php

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;
use Magento\Framework\Logger\Handler\Exception;
use Magento\Framework\Logger\Handler\System;
use ClassyLlama\AvaTax\Model\Config\Source\LogFileMode;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

use Magento\Framework\App\Config\ScopeConfigInterface;

class FileHandler extends System
{
    const XML_PATH_AVATAX_LOG_FILE_ENABLED = 'tax/avatax/logging_file_enabled';
    const XML_PATH_AVATAX_LOG_FILE_MODE = 'tax/avatax/logging_file_mode';
    const XML_PATH_AVATAX_LOG_FILE_LEVEL = 'tax/avatax/logging_file_level';
    const XML_PATH_AVATAX_LOG_FILE_DETAIL = 'tax/avatax/logging_file_detail';

    /**
     * @var string
     */
    protected $fileName = '/var/log/avatax.log';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var System
     */
    protected $systemHandler;

    /**
     * @param DriverInterface $filesystem
     * @param Exception $exceptionHandler
     * @param System $systemHandler
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $avaTaxConfig
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        Exception $exceptionHandler,
        System $systemHandler,
        ScopeConfigInterface $scopeConfig,
        Config $avaTaxConfig,
        IntrospectionProcessor $introspectionProcessor,
        WebProcessor $webProcessor,
        $filePath = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->systemHandler = $systemHandler;
        parent::__construct($filesystem, $exceptionHandler, $filePath);
        $this->setFormatter(new FileFormatter());
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    protected function addExtraProcessors(array $processors) {
        // Add additional processors for extra detail
        if ($this->logFileDetail() == LogDetail::EXTRA) {
            $this->processors = $processors;
        }
    }
    /**
     * @param null $store
     * @return bool
     */
    public function logFileEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int
     */
    public function logFileMode($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int
     */
    public function logFileLevel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int
     */
    public function logFileDetail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_FILE_DETAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }








    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->avaTaxConfig->isModuleEnabled() && $this->logFileEnabled() && $record['level'] >= $this->logFileLevel();
    }

    /**
     * @{inheritDoc}
     *
     * @param $record array
     * @return void
     */
    public function write(array $record)
    {
        // Filter the log details
        if ($this->logFileDetail() == LogDetail::MINIMAL && $record['level'] >= Logger::WARNING) {
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        } elseif ($this->logFileDetail() == LogDetail::NORMAL) {
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        } elseif ($this->logFileDetail() == LogDetail::EXTRA) {
            // do not remove any of the context data
        } else {
            if (isset($record['context']['request'])) unset($record['context']['request']);
            if (isset($record['context']['result'])) unset($record['context']['result']);
            if (isset($record['context']['additional'])) unset($record['context']['additional']);
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        }

        // Write the log file
        if ($this->logFileMode() == LogFileMode::COMBINED) {
            $this->systemHandler->handle($record);
        } else {
            parent::write($record);
        }
    }
}