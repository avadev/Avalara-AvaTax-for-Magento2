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
namespace ClassyLlama\AvaTax\BaseProvider\Logger\Handler;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use ClassyLlama\AvaTax\BaseProvider\Exception\AvalaraLoggerException;

/**
 * @codeCoverageIgnore
 */
class BaseAbstractHandler extends AbstractHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        #$this->setFormatter(new LineFormatter(null, null, true));
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
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
     * {@inheritdoc}
     */
    public function handle(array $record) : bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);
        #$record['formatted'] = $this->getFormatter()->format($record);
        $record['formatted'] = $record;
        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Write log
     *
     * @param $record array
     * @return void
     */
    public function write(array $record)
    {
        if (empty($record)) {
            throw new AvalaraLoggerException(__('No record found for logging.'));
        }
    }
    
    /**
     * If the record contains a request key in the context
     * return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getRequest(array $record)
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
    protected function getResult(array $record)
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
