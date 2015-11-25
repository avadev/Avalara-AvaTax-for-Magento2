<?php

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Logger;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ClassyLlama\AvaTax\Model\LogFactory;

class DbHandler extends AbstractHandler
{
    const XML_PATH_AVATAX_LOGGING_ENABLED = 'tax/avatax/enabled';
    const XML_PATH_AVATAX_LOG_LEVEL = 'tax/avatax/enabled';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LogFactory $logFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LogFactory $logFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logFactory = $logFactory;
        parent::__construct(Logger::DEBUG, true);
        $this->setFormatter(new LineFormatter(null, null, true));
    }

    /**
     * Return whether module is enabled
     *
     * @param null $store
     * @return mixed
     */
    public function isLoggingEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOGGING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return configured log level
     *
     * @param null $store
     * @return mixed
     */
    public function logLevel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_LOG_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }



    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->isLoggingEnabled() && $record['level'] >= $this->logLevel();
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
        $log = $this->logFactory->create()->setData(
            [
                'store_id' => isset($record['context']['store_id']) ? $record['context']['store_id'] : null,
                'activity' => isset($record['context']['activity']) ? $record['context']['activity'] : null,
                'source' => isset($record['context']['source']) ? $record['context']['source'] : null,
                'activity_status' => isset($record['context']['activity_status']) ? $record['context']['activity_status'] : null,
                'request' => isset($record['context']['request']) ? $record['context']['request'] : null,
                'result' => isset($record['context']['result']) ? $record['context']['result'] : null,
                'additional' => isset($record['context']['additional']) ? $record['context']['additional'] : null
            ]
        );
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