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
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use ClassyLlama\AvaTax\Model\LogFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;

/**
 * Monolog Handler for writing log entries to a database table
 */
class DbHandler extends AbstractHandler
{
    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $processors = array();

    /**
     * @param LogFactory $logFactory
     * @param Config $avaTaxConfig
     */
    public function __construct(
        LogFactory $logFactory,
        Config $avaTaxConfig
    ) {
        $this->logFactory = $logFactory;
        $this->avaTaxConfig = $avaTaxConfig;
        parent::__construct(Logger::DEBUG, true);
        $this->setFormatter(new LineFormatter(null, null, true));
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Checking config a value, and conditionally adding extra processors to the handler
     *
     * @param array $processors
     */
    protected function addExtraProcessors(array $processors) {
        // Add additional processors for extra detail
        if ($this->avaTaxConfig->getLogDbDetail() == LogDetail::EXTRA) {
            $this->processors = $processors;
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
        return $this->avaTaxConfig->isModuleEnabled() && $record['level'] >= $this->avaTaxConfig->getLogDbLevel();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Writes the log to the database by utilizing the Log model
     *
     * @param $record array
     * @return void
     */
    public function write(array $record): void
    {
        # Log to database
        /** @var \ClassyLlama\AvaTax\Model\Log $log */
        $log = $this->logFactory->create();

        $log->setData('level', isset($record['level_name']) ? $record['level_name'] : null);
        $log->setData('message', isset($record['message']) ? $record['message'] : null);

        if (isset($record['extra']['store_id'])) {
            $log->setData('store_id', $record['extra']['store_id']);
            unset($record['extra']['store_id']);
        }
        if (isset($record['extra']['class']) && isset($record['extra']['line'])) {
            $log->setData('source', $record['extra']['class'] . " [line:" . $record['extra']['line'] . "]");
        }

        if ($this->avaTaxConfig->getLogDbDetail() == LogDetail::MINIMAL && $record['level'] >= Logger::WARNING) {
            $log->setData('request', $this->getRequest($record));
            $log->setData('result', $this->getResult($record));
        } elseif ($this->avaTaxConfig->getLogDbDetail() == LogDetail::NORMAL) {
            $log->setData('request', $this->getRequest($record));
            $log->setData('result', $this->getResult($record));
            $log->setData('additional', $this->getContextVarExport($record));
        } elseif ($this->avaTaxConfig->getLogDbDetail() == LogDetail::EXTRA) {
            $log->setData('request', $this->getRequest($record));
            $log->setData('result', $this->getResult($record));
            $log->setData('additional',
                $this->getExtraVarExport($record) .
                (strlen($this->getExtraVarExport($record)) > 0 ? "\n" : '') .
                $this->getContextVarExport($record)
            );
        }
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

    /**
     * If the record contains a request key in the context
     * return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getRequest(array &$record)
    {
        $string = "";
        if (isset($record['context']['request'])) {
            $string = $record['context']['request'];
            unset($record['context']['request']);
        }
        return $string;
    }

    /**
     * If the record contains a result key in the context
     * return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getResult(array &$record)
    {
        $string = "";
        if (isset($record['context']['result'])) {
            $string = $record['context']['result'];
            unset($record['context']['result']);
        }
        return $string;
    }

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }
}
