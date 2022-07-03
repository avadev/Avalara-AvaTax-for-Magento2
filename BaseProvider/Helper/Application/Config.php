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
namespace ClassyLlama\AvaTax\BaseProvider\Helper\Application;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Avalara ApplicationLogger Config helper
 */
class Config extends AbstractHelper
{    
	const XML_PATH_APPLICATION_LOG_ENABLED = 'tax/baseprovider/logging_enabled';

    const XML_PATH_AVATAX_APPLICATION_LOG_MODE = 'tax/baseprovider/logging_mode';

    const XML_PATH_AVATAX_APPLICATION_LOG_LIMIT = 'tax/baseprovider/logging_limit';

    /**
     * Return configured log level
     *
     * @return int
     */
    public function getLogEnabled()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_APPLICATION_LOG_ENABLED);
    }

    /**
     * Return configured log detail
     *
     * @return int
     */
    public function getLogMode()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_AVATAX_APPLICATION_LOG_MODE);
    }

    /**
     * Return configured log limit
     *
     * @return int
     */
    public function getLogLimit()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_AVATAX_APPLICATION_LOG_LIMIT);
    }
}
