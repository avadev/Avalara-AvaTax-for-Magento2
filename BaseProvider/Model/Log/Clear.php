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
namespace ClassyLlama\AvaTax\BaseProvider\Model\Log;

use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\BaseProvider\Helper\Application\Config as ApplicationLoggerConfig;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log\CollectionFactory as LogCollFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use ClassyLlama\AvaTax\Helper\ApiLog;

class Clear
{
    /**
     * @var LoggerInterface
     */
    protected $applicationLogger;

    /**
     * @var LogCollFactory
     */
    protected $logCollFactory;

    /**
     * @var ApplicationLoggerConfig
     */
    protected $applicationLoggerConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;
    
    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param LoggerInterface $applicationLogger
     * @param ApplicationLoggerConfig $applicationLoggerConfig
     * @param LogCollFactory $logCollFactory
     * @param DateTime $dateTime
     * @param ApiLog $apiLog
     */
    public function __construct(
        LoggerInterface $applicationLogger,
        ApplicationLoggerConfig $applicationLoggerConfig,
        LogCollFactory $logCollFactory,
        DateTime $dateTime,
        ApiLog $apiLog
    ) {
        $this->applicationLogger = $applicationLogger;
        $this->applicationLoggerConfig = $applicationLoggerConfig;
        $this->logCollFactory = $logCollFactory;
        $this->dateTime = $dateTime;
        $this->apiLog = $apiLog;
    }

    /**
     * Initiates the clear logs and queue process
     *
     * @return void
     */
    public function process()
    {
        $this->applicationLogger->debug(__('Initiating log clearing from cron job'));
        $size = $this->clearDbLogs();
        $this->applicationLogger->debug(
            __('Completed log clearing from cron job. Total Deleted: ' . $size),
            [
                'delete_count' => $size,
                'extra' => [
                    'class' => __METHOD__
                ]
            ]
        );
    }

    /**
     * Clear Db logs
     *
     * @return int
     */
    public function clearDbLogs()
    {
        $limit = $this->applicationLoggerConfig->getLogLimit();
        if ($limit == '') {
            return 0;
        }
        $filteredDate = $this->getFilterDate($limit);
        $logs = $this->logCollFactory->create()
            ->addFieldToFilter('created_at', ['lteq' => $filteredDate]);
        $size = 0;
        /* echo $logs->getSelect()->__toString(); */
        foreach ($logs as $log) {
            try {
                $log->delete();
                $size++;
            } catch(\Exception $e) {
                $debugLogContext = [];
                $debugLogContext['message'] = $e->getMessage();
                $debugLogContext['source'] = 'clearlog';
                $debugLogContext['operation'] = 'BaseProvider_Model_Log_Clear';
                $debugLogContext['function_name'] = 'clearDbLogs';
                $this->apiLog->debugLog($debugLogContext);
                $e->getMessage();
            }
        }
        return $size;
    }

    private function getFilterDate($days)
    {
        if ($days == 0) {
            return $this->dateTime->gmtDate('Y-m-d');
        } else {
            return $this->dateTime->gmtDate('Y-m-d', strtotime('-' . $days . ' day'));
        }
        
    }
}
