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

class Company extends Rest implements RestCompanyInterface
{
    /**
     * @param \Avalara\AvaTaxClient $client
     * @param null                  $request
     *
     * @return mixed
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    protected function getCompaniesFromClient( $client, $request = null )
    {
        if ($request === null)
        {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = $client->queryCompanies(
            $request->getInclude(),
            $request->getFilter(),
            $request->getTop(),
            $request->getSkip(),
            $request->getOrderBy()
        );

        $this->validateResult( $clientResult, $request );

        return $this->formatResult( $clientResult )->getValue();
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanies(
        $request = null,
        $mode = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $mode, $scopeId, $scopeType );

        return $this->getCompaniesFromClient( $client, $request );
    }

    /**
     * @param      $accountNumber
     * @param      $password
     * @param null $request
     * @param null $mode
     *
     * @return mixed
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    public function getCompaniesWithSecurity( $accountNumber, $password, $request = null, $mode = null )
    {
        $client = $this->getClient( $mode );
        $client->withSecurity( $accountNumber, $password );

        return $this->getCompaniesFromClient( $client, $request );
    }
}