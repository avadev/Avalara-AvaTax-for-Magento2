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
use Exception;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Helper\ApiLog;

class Company extends Rest implements RestCompanyInterface
{
    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param ApiLog $apiLog
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        ApiLog $apiLog
    ) {
        $this->apiLog = $apiLog;
        parent::__construct($logger, $dataObjectFactory, $clientPool);
    }

    /**
     * REST call to get Companies
     *
     * @param \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper $client
     * @param DataObject|null       $request
     * 
     * @return DataObject[]
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    protected function getCompaniesFromClient( 
        $client, 
        $request = null, 
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE 
    )
    {
        if ($request === null)
        {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryCompanies(
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $debugLogContext = [];
            $debugLogContext['message'] = $clientException->getMessage();
            $debugLogContext['source'] = 'companies';
            $debugLogContext['operation'] = 'Framework_Interaction_Rest_Company';
            $debugLogContext['function_name'] = 'getCompaniesFromClient';
            $this->apiLog->debugLog($debugLogContext, $scopeId, $scopeType);
            $this->handleException($clientException, $request);
        } catch (\Throwable $exception) {
            $debugLogContext = [];
            $debugLogContext['message'] = $exception->getMessage();
            $debugLogContext['source'] = 'companies';
            $debugLogContext['operation'] = 'Framework_Interaction_Rest_Company';
            $debugLogContext['function_name'] = 'getCompaniesFromClient';
            $this->apiLog->debugLog($debugLogContext, $scopeId, $scopeType);
            throw $exception;
        }

        return $this->formatResult($clientResult)->getData('value');
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

        return $this->getCompaniesFromClient( $client, $request, $scopeId, $scopeType );
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
        $client->withCatchExceptions(false);
        // Override security credentials with custom ones
        $client->withSecurity( $accountNumber, $password );

        return $this->getCompaniesFromClient( $client, $request );
    }

    /**
     * @param bool|null       $isProduction
     * @param string|int|null $scopeId
     * @param string          $scopeType
     *
     * @return \Avalara\FetchResult
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    public function getCertificateExposureZones(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        try {
            $client = $this->getClient($isProduction, $scopeId, $scopeType);
            return $client->listCertificateExposureZones(null, null, null, null);
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // in case if Avatax account info is missing we will return false, no need to add CEP exception logging
            return false;
        } catch (\Exception $e){
            return false;
        }
    }
}
