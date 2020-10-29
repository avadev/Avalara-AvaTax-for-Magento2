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
use ClassyLlama\AvaTax\Exception\AddressValidateException;

interface RestAddressInterface extends \ClassyLlama\AvaTax\Api\RestInterface
{
    /**
     * Perform REST request to validate address
     *
     * @param \Magento\Framework\DataObject $request
     * @param string|null                   $isProduction
     * @param string|int|null               $scopeId
     * @param string                        $scopeType
     *
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result
     * @throws AddressValidateException
     * @throws AvataxConnectionException
     */
    public function validate( $request, $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
}
