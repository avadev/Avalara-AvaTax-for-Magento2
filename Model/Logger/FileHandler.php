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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception;
use Magento\Framework\Logger\Handler\System;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;
use ClassyLlama\AvaTax\Model\Config\Source\LogFileMode;
use Monolog\Handler\RotatingFileHandler;

/**
 * Monolog Hanlder for writing log entries to a custom file
 */
class FileHandler extends System
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/avatax.log';

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var System
     */
    protected $systemHandler;

    /**
     * @var RotatingFileHandler
     */
    protected $rotatingFileHandler;

    /**
     * @param DriverInterface $filesystem
     * @param Exception $exceptionHandler
     * @param System $systemHandler
     * @param Config $avaTaxConfig
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        Exception $exceptionHandler,
        System $systemHandler,
        Config $avaTaxConfig,
        $filePath = null
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->systemHandler = $systemHandler;
        parent::__construct($filesystem, $exceptionHandler, $filePath);

        // Set our custom formatter so that the context and extra parts of the record will print on multiple lines
        $this->setFormatter(new FileFormatter());
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
        $this->initializeRotatingLogs($filePath);
    }

    /**
     * @param array $processors
     */
    protected function addExtraProcessors(array $processors) {
        // Add additional processors for extra detail
        if (
            $this->avaTaxConfig->isModuleEnabled() &&
            $this->avaTaxConfig->getLogFileEnabled() &&
            $this->avaTaxConfig->getLogFileDetail() == LogDetail::EXTRA
        ) {
            $this->processors = $processors;
        }
    }

    /*
     * Performs any initialization for log file rotation
     */
    protected function initializeRotatingLogs($filePath)
    {
        // Add additional processors for extra detail
        if (
            $this->avaTaxConfig->isModuleEnabled() &&
            $this->avaTaxConfig->getLogFileEnabled() &&
            $this->avaTaxConfig->getLogFileMode() == LogFileMode::SEPARATE &&
            $this->avaTaxConfig->getLogFileBuiltinRotateEnabled()
        ) {
            $this->rotatingFileHandler = new RotatingFileHandler(
                $filePath ? $filePath . $this->fileName : BP . $this->fileName,
                $this->avaTaxConfig->getLogFileBuiltinRotateMaxFiles(),
                $this->avaTaxConfig->getLogFileLevel()
            );
        }
    }

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * Uses the admin configuration settings to determine if the record should be handled
     *
     * @param array $record
     * @return Boolean
     */
    public function isHandling(array $record): bool
    {
        return  $this->avaTaxConfig->isModuleEnabled() &&
                $this->avaTaxConfig->getLogFileEnabled() &&
                $record['level'] >= $this->avaTaxConfig->getLogFileLevel();
    }

    /**
     * Writes the log record to a file based on admin configuration settings
     *
     * @param $record array
     * @return void
     */
    public function write(array $record): void
    {
        // Filter the log details
        if ($this->avaTaxConfig->getLogFileDetail() == LogDetail::MINIMAL && $record['level'] >= Logger::WARNING) {
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        } elseif ($this->avaTaxConfig->getLogFileDetail() == LogDetail::NORMAL) {
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        } elseif ($this->avaTaxConfig->getLogFileDetail() == LogDetail::EXTRA) {
            // do not remove any of the context data
        } else {
            if (isset($record['context']['request'])) unset($record['context']['request']);
            if (isset($record['context']['result'])) unset($record['context']['result']);
            if (isset($record['context']['additional'])) unset($record['context']['additional']);
            if (isset($record['context']['extra'])) unset($record['context']['extra']);
        }

        // Write the log file
        if ($this->avaTaxConfig->getLogFileMode() == LogFileMode::COMBINED) {
            // forward the record to the default system handler for processing instead
            $this->systemHandler->handle($record);
        } elseif (
            $this->avaTaxConfig->getLogFileMode() == LogFileMode::SEPARATE &&
            $this->avaTaxConfig->getLogFileBuiltinRotateEnabled()
        ) {
            $this->writeWithRotation($record);
        } else {
            // write the log to the custom log file
            parent::write($record);
        }
    }

    /**
     * Writes the log record to a separate rotatingFileHandler
     *
     * @param $record array
     * @return void
     */
    public function writeWithRotation(array $record)
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir, DriverInterface::WRITEABLE_DIRECTORY_MODE);
        }

        // make sure the handler is at least there
        if ($this->rotatingFileHandler != null) {
            $this->rotatingFileHandler->write($record);
        } else {
            // write the log somewhere
            $record['message'] .= ' - ERROR ROTATING LOG FILE';
            parent::write($record);
        }
    }
}
