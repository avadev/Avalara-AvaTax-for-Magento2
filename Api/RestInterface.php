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

namespace ClassyLlama\AvaTax\Api;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

interface RestInterface
{
    /**
     * Get an AvaTax REST API client object
     *
     * @param null|bool       $isProduction
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );

    /**
     * Ping AvaTax REST service to verify connection/authentication
     *
     * @param null|bool       $isProduction
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return bool
     * @throws AvataxConnectionException
     * @throws \InvalidArgumentException
     */
    public function ping(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
