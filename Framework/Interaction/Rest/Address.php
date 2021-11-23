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

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\ResultFactory as AddressResultFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Address extends \ClassyLlama\AvaTax\Framework\Interaction\Rest
    implements \ClassyLlama\AvaTax\Api\RestAddressInterface
{
    /**
     * @var RestConfig
     */
    protected $restConfig;

    protected $addressResultFactory;

    /**
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param RestConfig $restConfig
     * @param AddressResultFactory $addressResultFactory
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        RestConfig $restConfig,
        AddressResultFactory $addressResultFactory
    ) {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->restConfig = $restConfig;
        $this->addressResultFactory = $addressResultFactory;
    }

    /**
     * Perform REST request to validate address
     *
     * @param \Magento\Framework\DataObject $request
     * @param bool|null                     $isProduction
     * @param string|int|null               $scopeId
     * @param string|null                   $scopeType
     *
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result
     * @throws AddressValidateException
     * @throws AvataxConnectionException
     */
    public function validate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    )
    {
        $client = $this->getClient( $isProduction, $scopeId, $scopeType );
        $client->withCatchExceptions(false);

        $address = $request->getAddress();
        $textCase = $request->getTextCase();

        try {
            $resultObj = $client->resolveAddress(
                $address->getLine1(),
                $address->getLine2(),
                $address->getLine3(),
                $address->getCity(),
                $address->getRegion(),
                $address->getPostalCode(),
                $address->getCountry(),
                $textCase,
                null,
                null
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException, $request);
        }

        $this->validateResult($resultObj, $request);

        $resultGeneric = $this->formatResult($resultObj);
        /** @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result $result */
        $result = $this->addressResultFactory->create(['data' => $resultGeneric->getData()]);

        return $result;
    }

    /**
     * Validate a response from the AvaTax library client
     * Response is an error message string if an error occurred
     *
     * @param string|\Avalara\PingResultModel $result
     * @param \Magento\Framework\DataObject|null $request
     * @return void
     * @throws AddressValidateException
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

            $this->logger->warning(__('AvaTax address validation warnings: %1', $warningsMsg), [
                'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);
        }

        if (!empty($errors)) {
            $errorsMsg = implode('; ', $errors);

            $this->logger->error(__('AvaTax address validation errors: %1', $errorsMsg), [
                'request' => (!is_null($request)) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);

            throw new AddressValidateException(__($errorsMsg));
        }
    }
}
