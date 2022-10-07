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

use ClassyLlama\AvaTax\Exception\DefinitionsException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Definitions\ResultFactory as DefinitionsResultFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Definitions extends \ClassyLlama\AvaTax\Framework\Interaction\Rest
    implements \ClassyLlama\AvaTax\Api\RestDefinitionsInterface
{
    const PARAMETERS_FILTER = 'id=621';
    /**
     * @var RestConfig
     */
    protected $restConfig;

    protected $definitionsResultFactory;

    /**
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param RestConfig $restConfig
     * @param DefinitionsResultFactory $definitionsResultFactory
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        RestConfig $restConfig,
        DefinitionsResultFactory $definitionsResultFactory
    ) {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->restConfig = $restConfig;
        $this->definitionsResultFactory = $definitionsResultFactory;
    }

    /**
     * Perform REST request to get Definitions parameters
     *
     * @param bool|null                     $isProduction
     * @param string|int|null               $scopeId
     * @param string|null                   $scopeType
     *
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Definitions\Result
     * @throws DefinitionsException
     * @throws AvataxConnectionException
     */
    public function parameters(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $isProduction, $scopeId, $scopeType );
        $client->withCatchExceptions(false);
        $filter = self::PARAMETERS_FILTER;
        try {
            $resultObj = $client->listParameters( $filter );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException);
        }

        $this->validateResult($resultObj);

        $resultGeneric = $this->formatResult($resultObj);
        /** @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Definitions\Result $result */
        $result = $this->definitionsResultFactory->create(['data' => $resultGeneric->getData()]);

        return $result;
    }

    /**
     * Validate a response from the AvaTax library client
     * Response is an error message string if an error occurred
     *
     * @param string|\Avalara\PingResultModel $result
     * @param \Magento\Framework\DataObject|null $request
     * @return void
     * @throws DefinitionsException
     */
    protected function validateResult($result, $request = null)
    {
        $errors = [];
        $warnings = [];
        if (isset($result->messages) && is_array($result->messages)) {
            foreach ($result->messages as $message) {
                if (in_array($message->severity, ['Error', 'Exception'])) {
                    $errors[] = $message->summary;
                } elseif (in_array($message->severity, $this->restConfig->getWarningSeverityLevels())) {
                    $warnings[] = $message->summary;
                }
            }
        }

        if (!empty($warnings)) {
            $warningsMsg = implode('; ', $warnings);

            $this->logger->warning(__('AvaTax definitions parameters warnings: %1', $warningsMsg), [
                'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);
        }

        if (!empty($errors)) {
            $errorsMsg = implode('; ', $errors);

            $this->logger->error(__('AvaTax definitions parameters errors: %1', $errorsMsg), [
                'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);

            throw new DefinitionsException(__($errorsMsg));
        }
    }
}
