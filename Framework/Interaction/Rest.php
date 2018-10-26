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

namespace ClassyLlama\AvaTax\Framework\Interaction;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Rest implements \ClassyLlama\AvaTax\Api\RestInterface
{
    const API_MODE_PROD = 'production';

    const API_MODE_DEV = 'sandbox';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ClientPool
     */
    protected $clientPool;

    /** @var array */
    protected $clients = [];

    /**
     * @param LoggerInterface   $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool        $clientPool
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool
    )
    {
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->clientPool = $clientPool;
    }

    /**
     * Get an AvaTax REST API client object
     *
     * @param null|bool       $isProduction
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return AvaTaxClientWrapper
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        return $this->clientPool->getClient($isProduction, $scopeId, $scopeType);
    }

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
    )
    {
        $result = null;

        try {
            $result = $this->getClient($isProduction, $scopeId, $scopeType)
                ->withCatchExceptions(false)
                ->ping();
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            $this->handleException($clientException);
        }

        return $result->authenticated;
    }

    /**
     * @param \GuzzleHttp\Exception\ClientException|\Exception $exception
     * @param DataObject|null                                  $request
     *
     * @throws AvataxConnectionException
     */
    protected function handleException($exception, $request = null)
    {
        $requestLogData = $request !== null ? var_export($request->getData(), true) : null;
        $logMessage = __('AvaTax connection error: %1', $exception->getMessage());
        $logContext = ['request' => $requestLogData];

        if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
            $responseBody = (string)$exception->getResponse()->getBody();
            $response = json_decode($responseBody, true);

            $logMessage = __('Response from AvaTax indicated non-specific error');
            $logContext['result'] = $responseBody;

            if ($response !== null) {
                $logMessage = __(
                    'AvaTax connection error: %1',
                    trim(
                        array_reduce(
                            $response['error']['details'],
                            function ($error, $detail) {
                                if ($detail['severity'] !== 'Exception') {
                                    return $error;
                                }

                                return $error . ' ' . $detail['description'];
                            },
                            ''
                        )
                    )
                );
                $logContext['result'] = var_export($response, true);
            }
        }

        $this->logger->error($logMessage, $logContext);
        throw new AvataxConnectionException($logMessage);
    }

    /**
     * Convert a simple object to a data object
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function formatResult($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = $this->formatResult($subValue);
            }
        } else if (is_object($value)) {
            $valueObj = $this->dataObjectFactory->create();
            foreach ($value as $key => $subValue) {
                $methodName = 'set' . ucfirst($key);
                call_user_func([$valueObj, $methodName], $this->formatResult($subValue));
            }

            $value = $valueObj;
        }

        return $value;
    }
}
