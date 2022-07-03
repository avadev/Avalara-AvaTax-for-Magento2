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
namespace ClassyLlama\AvaTax\BaseProvider\Logger;

use Monolog\Logger;

class GenericLogger extends Logger
{
    /**
     * Interesting events
     *
     * Examples: Admin Config Save etc...
     */
    const API_LOG = 1;

    /**
     * GenericLogger constructor.
  
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
        static::$levels[self::API_LOG] = 'APILOG';
    }
    
    /**
     * Adds a log record at the API_LOG level.
     *
     * @param  string $message The log message
     * @param  array  $context The log context
     * @return bool   Whether the record has been processed
     */
    public function addApiLog($message, array $context = array())
    {
        return $this->addRecord(static::API_LOG, $message, $context);
    }

    /**
     * Adds a log record at the API_LOG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array  $context The log context
     * @return bool   Whether the record has been processed
     */
    public function apiLog($message, array $context = array())
    {
        return $this->addRecord(static::API_LOG, $message, $context);
    }
}
