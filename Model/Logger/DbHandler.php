<?php

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\LogFactory;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Config\Source\LogDetail;

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
        Config $avaTaxConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logFactory = $logFactory;
        $this->avaTaxConfig = $avaTaxConfig;
        parent::__construct(Logger::DEBUG, true);
        $this->setFormatter(new LineFormatter(null, null, true));
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
        $log->setData('store_id', isset($record['context']['store_id']) ? $record['context']['store_id'] : null);
        $log->setData('level', isset($record['level_name']) ? $record['level_name'] : null);
        if (isset($record['context']['activity'])) {
            $log->setData('activity', isset($record['context']['activity']) ? $record['context']['activity'] : null);
            $log->setData('additional', $log->getData('additional') . isset($record['message']) ? $record['message'] : null);
        } else {
            $log->setData('activity', isset($record['message']) ? $record['message'] : null);
        }
        $log->setData('source', isset($record['context']['source']) ? $record['context']['source'] : null);
        $log->setData('activity_status', isset($record['context']['activity_status']) ? $record['context']['activity_status'] : null);
        if ($this->logDbDetail() == LogDetail::MINIMAL && $record['level'] >= Logger::WARNING) {
            $log->setData('request', isset($record['context']['request']) ? $record['context']['request'] : null);
            $log->setData('result', isset($record['context']['result']) ? $record['context']['result'] : null);
        } elseif ($this->logDbDetail() == LogDetail::NORMAL) {
            $log->setData('request', isset($record['context']['request']) ? $record['context']['request'] : null);
            $log->setData('result', isset($record['context']['result']) ? $record['context']['result'] : null);
            $log->setData('additional', $log->getData('additional') . isset($record['context']['additional']) ? $record['context']['additional'] : null);
        } elseif ($this->logDbDetail() == LogDetail::EXTRA) {
            $log->setData('request', isset($record['context']['request']) ? $record['context']['request'] : null);
            $log->setData('result', isset($record['context']['result']) ? $record['context']['result'] : null);
            $log->setData('additional',
                $log->getData('additional') .
                (isset($record['context']['additional']) ? $record['context']['additional'] : "") .
                (isset($record['context']['extra']) ? $record['context']['extra'] : "") .
                (isset($record['context']['session']) ? $record['context']['session'] : "")
            );
        }
        $log->save();
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