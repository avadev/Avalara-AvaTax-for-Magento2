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

/**
 * ApplicationLogger
 */
/**
 * @codeCoverageIgnore
 */
class ApplicationLogger extends Logger
{
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
    }
}
