<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Config\Source;

use Monolog\Logger;
use Magento\Framework\Option\ArrayInterface;

class LogLevel implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            // Debug - Any information necessary to be captured for context of what is happening for diagnostic purposes
            // Very verbose and excessive logging not intended for production use
            ['value' => Logger::DEBUG, 'label' => __('Debug')],

            // Info - Any normal interesting activity to be aware of having occurred
            // Detailed informational logging expected for production monitoring
            ['value' => Logger::INFO, 'label' => __('Info')],

            // Notice - Significant normal informational messages
            // Lighter informational logging expected for production monitoring
            ['value' => Logger::NOTICE, 'label' => __('Notice')],

            // Warning - Any event that is not optimal, but is handled or worked around and should not require intervention by a human
            // Something that could degrade site functionality or a customer's experience
            ['value' => Logger::WARNING, 'label' => __('Warning')],

            // Error - Something unexpected happened and a human should be made aware of the problem
            // Something that likely impacts the site's important functionality or would seriously affect customers
            ['value' => Logger::ERROR, 'label' => __('Error')],

            // Critical - Serious errors that require human intervention
            // Unhandled code exceptions needing the attention of a developer/admin
            ['value' => Logger::CRITICAL, 'label' => __('Critical')],

            //['value' => Logger::ALERT, 'label' => __('Alert')],
            //['value' => Logger::EMERGENCY, 'label' => __('Emergency')],
        ];
    }
}
