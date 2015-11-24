<?php

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\LogFactory;

/**
 * Auto Fill module base helper
 */
class Logger extends AbstractHelper
{
    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @param Context $context
     * @param AvaTaxLogger $avaTaxLogger
     * @param LogFactory $logFactory
     */
    public function __construct(
        Context $context,
        AvaTaxLogger $avaTaxLogger,
        LogFactory $logFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->logFactory = $logFactory;
        parent::__construct($context);
    }

    /**
     * Convenience method for logging
     * Example after constructor injection: $this->logHelper->avaTaxLog("log contents");
     *
     * @param $store_id int
     * @param $activity string
     * @param $source string
     * @param $activity_status string
     * @param $request string
     * @param $result string
     * @param $additional string
     */
    public function avaTaxLog($store_id, $activity, $source, $activity_status, $request, $result, $additional)
    {
        # Log to custom file
        $this->avaTaxLogger->info(
            $additional,
            array( /* context */
                'store_id' => $store_id,
                'activity' => $activity,
                'source' => $source,
                'actibity_status' => $activity_status
            )
        );

        # Log to database
        /** @var \ClassyLlama\AvaTax\Model\Log $log */
        $log = $this->logFactory->create()->setData(
            [
                'store_id' => $store_id,
                'activity' => $activity,
                'source' => $source,
                'activity_status' => $activity_status,
                'request' => $request,
                'result' => $result,
                'additional' => $additional
            ]
        );
        $log->save();
    }
}
