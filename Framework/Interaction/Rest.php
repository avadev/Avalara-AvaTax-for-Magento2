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

use \ClassyLlama\AvaTax\Helper\AvaTaxClientWrapper;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
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
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool
    ) {
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
        return $this->clientPool->getClient( $isProduction, $scopeId, $scopeType );
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
    public function ping( $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $result = $this->getClient( $isProduction, $scopeId, $scopeType)->ping();

        $this->validateResult($result);

        return $result->authenticated;
    }

    /**
     * Validate a response from the AvaTax library client
     * Response is an error message string if an error occurred
     *
     * @param string|\Avalara\PingResultModel $result
     * @param \Magento\Framework\DataObject|null $request
     * @return void
     * @throws AvataxConnectionException
     */
    protected function validateResult($result, $request = null)
    {
        if (!is_object($result)) {
            if (is_string($result)) {
                $this->logger->error(__('AvaTax connection error: %1', $result), [
                    'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                ]);
            } else {
                $this->logger->error(__('Response from AvaTax was in invalid format'), [
                    'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                    'result' => var_export($result, true),
                ]);
            }
            throw new AvataxConnectionException(__('AvaTax connection error'));
        }

        /**
         * This really should never happen, because the response should come back with a response code that
         * results in the Guzzle middleware throwing an exception, which Avalara catches and turns into a flat string result
         */
        if (isset($result->error)) {
            if (is_object($result->error) && isset($result->error->message)) {
                $this->logger->error(__('AvaTax connection error: %1', $result->error->message), [
                    'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                    'result' => var_export($result, true),
                ]);
            } else {
                $this->logger->error(__('Response from AvaTax indicated non-specific error'), [
                    'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                    'result' => var_export($result, true),
                ]);
            }
            throw new AvataxConnectionException(__('AvaTax connection error'));
        }
    }

    /**
     * Convert a simple object to a data object
     *
     * @param mixed $value
     * @return mixed
     */
    protected function formatResult($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = $this->formatResult($subValue);
            }
        } elseif (is_object($value)) {
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
