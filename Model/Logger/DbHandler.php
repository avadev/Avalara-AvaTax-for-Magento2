<?php

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\LogFactory;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DbHandler extends AbstractHandler
{
    const XML_PATH_AVATAX_LOG_DB_LEVEL = 'tax/avatax/logging_db_level';
    const XML_PATH_AVATAX_LOG_DB_DETAIL = 'tax/avatax/logging_db_detail';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LogFactory $logFactory
     * @param Config $avaTaxConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LogFactory $logFactory,
        Config $avaTaxConfig,
        IntrospectionProcessor $introspectionProcessor,
        WebProcessor $webProcessor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logFactory = $logFactory;
        $this->avaTaxConfig = $avaTaxConfig;
        parent::__construct(Logger::DEBUG, true);
        $this->setFormatter(new LineFormatter(null, null, true));
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    protected function addExtraProcessors(array $processors) {
        // Add additional processors for extra detail
        if ($this->logDbDetail() == LogDetail::EXTRA) {
            $this->processors = $processors;
        }
    }

    /**
     * Return configured log level
     *
     * @param null $store
     * @return int
     */
    public function logDbLevel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_DB_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log detail
     *
     * @param null $store
     * @return int
     */
    public function logDbDetail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_DB_DETAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }







    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->avaTaxConfig->isModuleEnabled() && $record['level'] >= $this->logDbLevel();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
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
     * @{inheritDoc}
     *
     * @param $record array
     * @return void
     */
    public function write(array $record)
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

        if ($this->logDbDetail() == LogDetail::MINIMAL && $record['level'] >= Logger::WARNING) {
            $log->setData('request', $this->getRequest($record));
            $log->setData('result', $this->getResult($record));
        } elseif ($this->logDbDetail() == LogDetail::NORMAL) {
            $log->setData('request', $this->getRequest($record));
            $log->setData('result', $this->getResult($record));
            $log->setData('additional', $this->getContextVarExport($record));
        } elseif ($this->logDbDetail() == LogDetail::EXTRA) {
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
     * @param array $record
     * @return string
     */
    protected function getContextVarExport(array $record)
    {
        $string = "";
        if (isset($record['context']) && count($record['context']) > 0) {
            $string = 'context: ' . var_export($record['context'], 1);
        }
        return $string;
    }

    /**
     * @param array $record
     * @return string
     */
    protected function getExtraVarExport(array $record)
    {
        $string = "";
        if (isset($record['extra']) && count($record['extra']) > 0) {
            $string = 'extra: ' . var_export($record['extra'], 1);
        }
        return $string;
    }

    /**
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