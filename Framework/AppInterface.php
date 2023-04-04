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

namespace ClassyLlama\AvaTax\Framework;

interface AppInterface
{
    /**
     * Connector version
     */
    const APP_VERSION = '2.5.1';
	/**
     * Avalara APP String
     */
    const CONNECTOR_STRING = 'a0o5a000007hWJMAA2';
	/**
     * Avalara Connector name
     */
    const CONNECTOR_NAME = 'Magento for SalesTax';
	/**
     * Avalara APP name
     */
    const APP_NAME = self::CONNECTOR_NAME.' || '.self::APP_VERSION.'v2';
}
