<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception;
use Magento\Framework\Logger\Handler\System;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;
use ClassyLlama\AvaTax\Model\Config\Source\LogFileMode;

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
     * @param DriverInterface $filesystem
     * @param Exception $exceptionHandler
     * @param System $systemHandler
     * @param Config $avaTaxConfig
     * @param IntrospectionProcessor $introspectionProcessor
     * @param WebProcessor $webProcessor
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        Exception $exceptionHandler,
        System $systemHandler,
        Config $avaTaxConfig,
        IntrospectionProcessor $introspectionProcessor,
        WebProcessor $webProcessor,
        $filePath = null
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->systemHandler = $systemHandler;
        parent::__construct($filesystem, $exceptionHandler, $filePath);

        // Set our custom formatter so that the context and extra parts of the record will print on multiple lines
        $this->setFormatter(new FileFormatter());
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    protected function addExtraProcessors(array $processors) {
        // Add additional processors for extra detail
        if ($this->avaTaxConfig->getLogFileDetail() == LogDetail::EXTRA) {
            $this->processors = $processors;
        }
    }

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * Uses the admin configuration settings to determine if the record should be handled
     *
     * @author Matt Johnson <matt.johnson@classyllama.com>
     * @param array $record
     * @return Boolean
     */
    public function isHandling(array $record)
    {
        return  $this->avaTaxConfig->isModuleEnabled() &&
                $this->avaTaxConfig->getLogFileEnabled() &&
                $record['level'] >= $this->avaTaxConfig->getLogFileLevel();
    }

    /**
     * Writes the log record to a file based on admin configuration settings
     *
     * @author Matt Johnson <matt.johnson@classyllama.com>
     * @param $record array
     * @return void
     */
    public function write(array $record)
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
        } else {
            // write the log to the custom log file
            parent::write($record);
        }
    }
}