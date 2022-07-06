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

use ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application\LoggingMode;
use Monolog\Logger;
use ClassyLlama\AvaTax\BaseProvider\Logger\Handler\BaseAbstractHandler;
use ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config;
use ClassyLlama\AvaTax\BaseProvider\Model\LogFactory;

/**
 * @codeCoverageIgnore
 */
class DbHandler extends BaseAbstractHandler
{
    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Config
     */
    protected $loggerConfig;

    public function __construct(
        Config $config,
        LogFactory $logFactory
    ) {
        $this->logFactory = $logFactory;
        $this->loggerConfig = $config;
        parent::__construct(Logger::DEBUG, true);
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
        return $this->loggerConfig->getLogEnabled() && ($this->loggerConfig->getLogMode() == LoggingMode::LOGGING_MODE_DB);
    }

    /**
     * Writes the log to the database by utilizing the Log model
     *
     * @param $record array
     * @return void
     */
    public function write(array $record) : void
    {
        parent::write($record);
        # Log to database
        /** @var \ClassyLlama\AvaTax\BaseProvider\Model\Log $log */
        $log = $this->logFactory->create();

        $log->setData('level', isset($record['level_name']) ? $record['level_name'] : null);
        $log->setData('message', isset($record['message']) ? $record['message'] : null);

        if (isset($record['extra']['store_id'])) {
            $log->setData('store_id', $record['extra']['store_id']);
            unset($record['extra']['store_id']);
        }
        if (isset($record['context']['extra']['class'])) {
            $log->setData('source', $record['context']['extra']['class']);
        } elseif (isset($record['extra']['class']) && isset($record['extra']['line'])) {
            $log->setData('source', $record['extra']['class'] . " [line:" . $record['extra']['line'] . "]");
        }

        $log->setData('request', $this->getRequest($record));
        $log->setData('result', $this->getResult($record));
        $log->setData(
            'additional',
            $this->getExtraVarExport($record)
        );
        $log->save();
    }

    /**
     * If the record contains a context key
     * export the variable contents and return it
     *
     * @param array $record
     * @return string
     */
    protected function getContextVarExport(array $record)
    {
        $string = "";
        if (isset($record['context']) && count($record['context']) > 0) {
            $string = 'context: ' . var_export($record['context'], true);
        }
        return $string;
    }

    /**
     * If the record contains a extra key in the context
     * export the variable contents, return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getExtraVarExport(array $record)
    {
        $string = "";
        if (isset($record['extra']) && count($record['extra']) > 0) {
            $string = 'extra: ' . var_export($record['extra'], true);
        }
        return $string;
    }
}
