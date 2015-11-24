<?php

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

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
     * @param Context $context
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        Context $context,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        parent::__construct($context);
    }

    /**
     * Convenience method for logging
     * Example after constructor injection: $this->logHelper->avaTaxLog("log contents");
     *
     * @param $message
     */
    public function avaTaxLog($message)
    {
        $this->avaTaxLogger->info($message);
    }
}
