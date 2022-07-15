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
namespace ClassyLlama\AvaTax\BaseProvider\Logger\Handler\Application;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application\LoggingMode;
use ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config;

/**
 * @codeCoverageIgnore
 */
class FileHandler extends Base
{
    /**
     * File name without extension
     */
    const FILENAME = 'logger';

    /**
     * Location to store the file
     */
    const FILEPATH = 'var/log/avalara/';

    /**
     * @var string
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var DriverInterface
     */
    protected $filesystem;

    /**
     * @var Config
     */
    protected $loggerConfig;

    /**
     * @param Config $config
     * @param DriverInterface $filesystem
     */
    public function __construct(
        Config $config,
        DriverInterface $filesystem
    ) {
        $this->loggerConfig = $config;
        $this->filesystem = $filesystem;

        parent::__construct(
            $filesystem,
            self::FILEPATH,
            $this->getFileName()
        );

        // Set our custom formatter so that the context and extra parts of the record will print on multiple lines
        $this->setFormatter(new LineFormatter());
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    public function getFileName()
    {
        return self::FILENAME . '-' . date('d-m-y') . ".log";
    }

    /**
     * Checking config a value, and conditionally adding extra processors to the handler
     *
     * @param array $processors
     */
    protected function addExtraProcessors(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * Uses the admin configuration settings to determine if the record should be handled
     *
     * @param array $record
     * @return Boolean
     */
    public function isHandling(array $record) : bool
    {
        return $this->loggerConfig->getLogEnabled() && ($this->loggerConfig->getLogMode() == LoggingMode::LOGGING_MODE_FILE);
    }

    /**
     * Writes the log to the filesystem
     *
     * @param $record array
     * @return void
     */
    public function write(array $record) : void
    {
        // Custom parsing can be added here
        parent::write($record);
    }
}
