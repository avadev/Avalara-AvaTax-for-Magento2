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

interface RestTaxInterface extends \ClassyLlama\AvaTax\Api\RestInterface
{
    const FLAG_FORCE_NEW_RATES = 'force_new_rates';

    /**
     * REST call to post tax transaction
     *
     * @param \Magento\Framework\DataObject $request
     * @param null|string                   $isProduction
     * @param null|string|int               $scopeId
     * @param string                        $scopeType
     * @param array                         $params
     *
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws AvataxConnectionException
     * @throws \Exception
     */
    public function getTax( $request, $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $params = []);
}
