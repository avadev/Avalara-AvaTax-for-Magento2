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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest;

use ClassyLlama\AvaTax\Api\RestCompanyInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use Magento\Framework\DataObject;

class Company extends Rest implements RestCompanyInterface
{
    /**
     * @param \Avalara\AvaTaxClient $client
     * @param DataObject|null       $request
     *
     * @return DataObject[]
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    protected function getCompaniesFromClient( $client, $request = null )
    {
        if ($request === null)
        {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = $client->queryCompanies(
            $request->getData('include'),
            $request->getData('filter'),
            $request->getData('top'),
            $request->getData('skip'),
            $request->getData('order_by')
        );

        $this->validateResult( $clientResult, $request );

        return $this->formatResult( $clientResult )->getData('value');
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanies(
        $request = null,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $isProduction, $scopeId, $scopeType );

        return $this->getCompaniesFromClient( $client, $request );
    }

    /**
     * @param string          $accountNumber
     * @param string          $password
     * @param DataObject|null $request
     * @param bool|null       $isProduction
     *
     * @return DataObject[]
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    public function getCompaniesWithSecurity( $accountNumber, $password, $request = null, $isProduction = null )
    {
        $client = $this->getClient( $isProduction );
        $client->withSecurity( $accountNumber, $password );

        return $this->getCompaniesFromClient( $client, $request );
    }
}