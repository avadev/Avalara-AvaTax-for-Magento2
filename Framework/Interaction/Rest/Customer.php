<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest;

use ClassyLlama\AvaTax\Api\RestCustomerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Customer as CustomerHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Customer extends Rest implements RestCustomerInterface
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param CustomerHelper    $customerHelper
     * @param Config            $config
     * @param LoggerInterface   $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool        $clientPool
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Config $config,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool
    )
    {
        parent::__construct( $logger, $dataObjectFactory, $clientPool );

        $this->customerHelper = $customerHelper;
        $this->config = $config;
    }

    /**
     * Perform REST request to get companies associated with the account
     *
     * @param DataObject      $request
     * @param bool|null       $isProduction
     * @param string|int|null $scopeId
     * @param string          $scopeType
     *
     * @return DataObject[]
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    public function getCertificatesList(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $isProduction, $scopeId, $scopeType );

        if ($request->getData( 'customer_code' ))
        {
            throw new \InvalidArgumentException( 'Must include a request with customer id' );
        }

        $clientResult = $client->listCertificatesForCustomer(
            $this->config->getCompanyId( $scopeId, $scopeType ),
            $this->customerHelper->getCustomerCode( $request->getData( 'customer_id' ), null, $scopeId ),
            $request->getData( 'include' ),
            $request->getData( 'filter' ),
            $request->getData( 'top' ),
            $request->getData( 'skip' ),
            $request->getData( 'order_by' )
        );

        $this->validateResult( $clientResult, $request );

        $certificates = $this->formatResult( $clientResult )->getValue();

        return $certificates;
    }

    /**
     * Perform REST request to get companies associated with the account
     *
     * @param DataObject      $request
     * @param bool|null       $isProduction
     * @param string|int|null $scopeId
     * @param string          $scopeType
     *
     * @return mixed
     */
    public function downloadCertificate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $isProduction, $scopeId, $scopeType );

        return $client->downloadCertificateImage(
            $this->config->getCompanyId( $scopeId, $scopeType ),
            $request->getData( 'id' ),
            $request->getData( 'page' ),
            $request->getData( 'type' )
        );
    }
}