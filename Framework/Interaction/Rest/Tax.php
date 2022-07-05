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

use Avalara\BatchAdjustTransactionModel;
use Avalara\CreateTransactionBatchRequestModel;
use Avalara\TransactionBatchItemModel;
use Avalara\TransactionBuilder;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Factory\TransactionBuilderFactory;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory as TaxResultFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use ClassyLlama\AvaTax\Helper\ApiLog;

class Tax extends Rest
    implements RestTaxInterface
{
    const LINE_PARAM_NAME_UNIT_NAME = 'AvaTax.LandedCost.UnitName';
    const LINE_PARAM_NAME_UNIT_AMT = 'AvaTax.LandedCost.UnitAmount';
    const LINE_PARAM_NAME_PREF_PROGRAM = 'AvaTax.LandedCost.PreferenceProgram';
    const TRANSACTION_PARAM_NAME_SHIPPING_MODE = 'AvaTax.LandedCost.ShippingMode';

    /**
     * @var TransactionBuilderFactory
     */
    protected $transactionBuilderFactory;

    /**
     * @var TaxResultFactory
     */
    protected $taxResultFactory;

    /**
     * @var RestConfig
     */
    protected $restConfig;

    /**
     * @var CustomsConfig
     */
    protected $customsConfigHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param TransactionBuilderFactory $transactionBuilderFactory
     * @param TaxResultFactory $taxResultFactory
     * @param RestConfig $restConfig
     * @param CustomsConfig $customsConfigHelper
     * @param Config $config
     * @param ApiLog $apiLog
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        TransactionBuilderFactory $transactionBuilderFactory,
        TaxResultFactory $taxResultFactory,
        RestConfig $restConfig,
        CustomsConfig $customsConfigHelper,
        Config $config,
        ApiLog $apiLog
    ) {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->transactionBuilderFactory = $transactionBuilderFactory;
        $this->taxResultFactory = $taxResultFactory;
        $this->restConfig = $restConfig;
        $this->customsConfigHelper = $customsConfigHelper;
        $this->config = $config;
        $this->apiLog = $apiLog;
    }

    /**
     * REST call to post tax transaction
     *
     * @param DataObject $request
     * @param null|bool $isProduction
     * @param null|string|int $scopeId
     * @param string $scopeType
     * @param array $params
     *
     * @return Result
     * @throws LocalizedException
     * @throws AvataxConnectionException
     * @throws Exception
     */
    public function getTax( $request, $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $params = [])
    {
        $exeEndTime = $apiStartTime = $apiEndTime = 0;
        $exeStartTime = microtime(true);
        $sendLog = true;
        if ($request->hasCode()) {
            $logContext = ['extra' => ['DocCode' => $request->getCode()]];
        }
       
        $client = $this->getClient( $isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);

        /** @var \Avalara\TransactionBuilder $transactionBuilder */
        $transactionBuilder = $this->transactionBuilderFactory->create([
            'client' => $client,
            'companyCode' => $request->getCompanyCode(),
            'type' => $request->getType(),
            'customerCode' => $request->getCustomerCode(),
            'dateTime' => $request->getDate(),
        ]);

        $this->setTransactionDetails($transactionBuilder, $request);
        $this->setLineDetails($transactionBuilder, $request);
        $logContext['extra']['LineCount'] = $transactionBuilder->getCurrentLineNumber() - 1;
        $this->setAddressDetails($transactionBuilder, $request);

        $resultObj = null;
        // Fallback to the old request data in case the `createAdjustmentRequest` method changes in the future
        $requestData = $request->getData();
        // Grab the request, which is the model. This is private, but this method gives us access
        $createAdjustmentRequest = $transactionBuilder->createAdjustmentRequest(null, null);

        if(isset($createAdjustmentRequest['newTransaction'])) {
            $requestData = $createAdjustmentRequest['newTransaction'];
        }

        try {
            $apiStartTime = microtime(true);
            $resultObj = $transactionBuilder->create();
            $apiEndTime = microtime(true);
        }
        catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $sendLog = false;
            $this->handleException($clientException, $request);
        }

        

        $resultGeneric = $this->formatResult($resultObj);
        /** @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result $result */
        $result = $this->taxResultFactory->create(['data' => $resultGeneric->getData()]);
        // TODO: Could be done better by undoing the recursive `formatResult` call, but that seems wasteful
        $result->setData('raw_result', $resultObj);
        $result->setData('raw_request', $requestData);

        /**
         * We store the request on the result so we can map request items to response items
         */
        $result->setRequest($request);

        if ($sendLog) {
            $exeEndTime = microtime(true);
            $prefix = '';
            $eventBlock = '';
            $docType = '';
            switch ($request->getType()) {
                case \Avalara\DocumentType::C_RETURNINVOICE :
                    $eventBlock = "CreditMemoPostCalculateTax";
                    $docType = "REFUND";
                    $prefix = 'CM';
                    $logContext['extra']['DocCode'] = $prefix . $request->getPurchaseOrderNo() ;
                    break;
                case \Avalara\DocumentType::C_SALESINVOICE :
                    $eventBlock = "InvoicePostCalculateTax";
                    $docType = "INVOICE";
                    $prefix = 'INV';
                    $logContext['extra']['DocCode'] = $prefix . $request->getPurchaseOrderNo() ;
                    break;
                case \Avalara\DocumentType::C_SALESORDER :
                    $eventBlock = "PostCalculateTax";
                    $docType = "SalesOrder";
                    break;
            }
            if (!empty($docType)) {
                $log['DocCode'] = $request->getPurchaseOrderNo();
                $logContext['extra']['EventBlock'] = $eventBlock;
                $logContext['extra']['DocType'] = $docType;
                $logContext['extra']['ConnectorTime'] = ['start' => $exeStartTime, 'end' => $exeEndTime];
                $logContext['extra']['ConnectorLatency'] = ['start' => $apiStartTime, 'end' => $apiEndTime];
                $logContext['source'] = 'tax';
                $logContext['operation'] = 'calculateTax';
                $logContext['function_name'] = __METHOD__;
                $this->apiLog->makeTransactionRequestLog($logContext, $scopeId, $scopeType);
            }
        }

        return $result;
    }

    /**
     * Set transaction-level fields for request
     *
     * @param TransactionBuilder $transactionBuilder
     * @param DataObject $request
     */
    protected function setTransactionDetails($transactionBuilder, $request)
    {
        if ($request->getCommit()) {
            $transactionBuilder->withCommit();
        }
        if ($request->hasIsSellerImporterOfRecord()) {
            $transactionBuilder->withSellerIsImporterOfRecord($request->getIsSellerImporterOfRecord());
        }

        if ($request->hasCode()) {
            $transactionBuilder->withTransactionCode($request->getCode());
        }
        if ($request->hasBusinessIdentificationNo()) {
            $transactionBuilder->withBusinessIdentificationNo($request->getBusinessIdentificationNo());
        }
        if ($request->hasCurrencyCode()) {
            $transactionBuilder->withCurrencyCode($request->getCurrencyCode());
        }
        if ($request->hasEntityUseCode()) {
            $transactionBuilder->withEntityUseCode($request->getEntityUseCode());
        }
        if ($request->hasDiscount()) {
            $transactionBuilder->withDiscountAmount($request->getDiscount());
        }
        if ($request->hasExchangeRate()) {
            $transactionBuilder->withExchangeRate($request->getExchangeRate(),
                $request->getExchangeRateEffectiveDate());
        }
        if ($request->hasReportingLocationCode()) {
            $transactionBuilder->withReportingLocationCode($request->getReportingLocationCode());
        }
        if ($request->hasPurchaseOrderNo()) {
            $transactionBuilder->withPurchaseOrderNo($request->getPurchaseOrderNo());
        }
        if ($request->hasReferenceCode()) {
            $transactionBuilder->withReferenceCode($request->getReferenceCode());
        }
        if ($request->hasTaxOverride()) {
            $override = $request->getTaxOverride();
            if (is_object($override)) {
                $transactionBuilder->withTaxOverride($override->getType(), $override->getReason(),
                    $override->getTaxAmount(), $override->getTaxDate());
            }
        }
//        if($request->hasShippingMode()) {
//            $transactionBuilder->withParameter(self::TRANSACTION_PARAM_NAME_SHIPPING_MODE, $request->getShippingMode());
//        }
    }

    /**
     * Set address entries and fields for request
     *
     * @param TransactionBuilder $transactionBuilder
     * @param DataObject $request
     * @throws Exception
     */
    protected function setLineDetails($transactionBuilder, $request)
    {
        if ($request->hasLines()) {
            foreach ($request->getLines() as $line) {
                $amount = ($line->hasAmount()) ? $line->getAmount() : 0;
                $transactionBuilder->withLine($amount, $line->getQuantity(), $line->getItemCode(), $line->getTaxCode());

                if ($line->getTaxIncluded()) {
                    $transactionBuilder->withLineTaxIncluded();
                }

                if ($line->hasDescription()) {
                    $transactionBuilder->withLineDescription($line->getDescription());
                }
                if ($line->hasDiscounted()) {
                    $transactionBuilder->withItemDiscount($line->getDiscounted());
                }
                if ($line->hasRef1() || $line->hasRef2()) {
                    $transactionBuilder->withLineCustomFields($line->getRef1(), $line->getRef2());
                }

                if ($this->customsConfigHelper->enabled()) {
                    if ($line->hasHsCode() && $line->getHsCode() !== '') {
                        $transactionBuilder->withLineHsCode($line->getHsCode());
                    }
                    if ($line->hasUnitName() && $line->getUnitName() !== '') {
                        $transactionBuilder->withLineParameter(self::LINE_PARAM_NAME_UNIT_NAME, $line->getUnitName());
                    }
                    if ($line->hasUnitAmount() && $line->getUnitAmount() !== '') {
                        $transactionBuilder->withLineParameter(self::LINE_PARAM_NAME_UNIT_AMT, $line->getUnitAmount());
                    }
                    if ($line->hasPreferenceProgram() && $line->getPreferenceProgram() !== '') {
                        $transactionBuilder->withLineParameter(self::LINE_PARAM_NAME_PREF_PROGRAM,
                            $line->getPreferenceProgram());
                    }
                }

                /**
                 * It's only here that we can set the line number on the request items, when we're sure it will be the same as the line number in the response
                 */
                $line->setNumber($transactionBuilder->getCurrentLineNumber());
            }
        }
    }

    /**
     * Set line item entries and fields for request
     *
     * @param TransactionBuilder $transactionBuilder
     * @param DataObject $request
     */
    protected function setAddressDetails($transactionBuilder, $request)
    {
        if ($request->hasAddresses()) {
            foreach ($request->getAddresses() as $type => $address) {
                $transactionBuilder->withAddress(
                    $type,
                    $address->getLine1(),
                    $address->getLine2(),
                    $address->getLine3(),
                    $address->getCity(),
                    $address->getRegion(),
                    $address->getPostalCode(),
                    $address->getCountry()
                );
            }
        }
    }

    /**
     * @param $requests
     * @param null $isProduction
     * @param null $scopeId
     * @param string $scopeType
     * @return Result
     * @throws AvataxConnectionException
     */
    public function getTaxBatch(
        $requests,
        $isProduction = null,
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ): Result {
        $exeEndTime = $apiStartTime = $apiEndTime = 0;
        $exeStartTime = microtime(true);
        $sendLogs = true;
        $client = $this->getClient($isProduction, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        $transactions = [];
        $logs = [];
        foreach ($requests as $request) {
            $log = [];
            $log['DocType'] = $request->getType();
            $log['DocCode'] = $request->getPurchaseOrderNo();
            $sendLog = true;
            $transactionBuilder = $this->transactionBuilderFactory->create([
                'client'       => $client,
                'companyCode'  => $request->getCompanyCode(),
                'type'         => $request->getType(),
                'customerCode' => $request->getCustomerCode(),
                'dateTime'     => $request->getDate(),
            ]);
            $this->setTransactionDetails($transactionBuilder, $request);
            try {
                $this->setLineDetails($transactionBuilder, $request);
                $log['LineCount'] = $transactionBuilder->getCurrentLineNumber() - 1;
            } catch (Exception $e) {
                $sendLog = false;
            }
            $this->setAddressDetails($transactionBuilder, $request);
            $createAdjustmentRequest = $transactionBuilder->createAdjustmentRequest(null, null);

            $requestData = $createAdjustmentRequest['newTransaction'];

            $transaction = new TransactionBatchItemModel();
            $transaction->createTransactionModel = $requestData;
            $transactions[] = $transaction;
            if ($sendLog)
                $logs[] = $log;
        }
        $transactionBatchRequestModel = new CreateTransactionBatchRequestModel();
        $transactionBatchRequestModel->name = "Batch" . date("Y-m-d H:i:s");
        $transactionBatchRequestModel->transactions = $transactions;

        $resultObj = null;
        try {
            $apiStartTime = microtime(true);
            $resultObj = $client->createTransactionBatch($this->config->getCompanyId(), $transactionBatchRequestModel);
            $apiEndTime = microtime(true);
        } catch (RequestException $clientException) {
            $sendLogs = false;
            $this->handleException($clientException);
        }
        $resultGeneric = $this->formatResult($resultObj);
        $result = $this->taxResultFactory->create(['data' => $resultGeneric->getData()]);
        $result->setData('raw_result', $resultObj);
        $result->setData('raw_request', $transactionBatchRequestModel);

        /**
         * We store the request on the result so we can map request items to response items
         */
        $result->setRequest($transactionBatchRequestModel);

        if ($sendLogs && count($logs) > 0) {
            $exeEndTime = microtime(true);
            foreach ($logs as $log) {
                $prefix = '';
                $eventBlock = '';
                $docType = '';
                switch ($log['DocType']) {
                    case \Avalara\DocumentType::C_RETURNINVOICE :
                        $eventBlock = "BatchCreditMemoPostCalculateTax";
                        $docType = "REFUND";
                        $prefix = 'CM';
                        break;
                    case \Avalara\DocumentType::C_SALESINVOICE :
                        $eventBlock = "BatchInvoicePostCalculateTax";
                        $docType = "INVOICE";  
                        $prefix = 'INV';
                        break;
                }
                if (empty($docType)) continue;
                $logContext = [];
                $logContext['extra']['DocCode'] = $prefix.$log['DocCode'];
                $logContext['extra']['DocType'] = $docType;
                if (isset($log['LineCount']))
                    $logContext['extra']['LineCount'] = $log['LineCount'];
                $logContext['extra']['EventBlock'] = $eventBlock;
                $logContext['extra']['ConnectorTime'] = ['start' => $exeStartTime, 'end' => $exeEndTime];
                $logContext['extra']['ConnectorLatency'] = ['start' => $apiStartTime, 'end' => $apiEndTime];
                $logContext['source'] = 'tax';
                $logContext['operation'] = 'calculateTax';
                $logContext['function_name'] = __METHOD__;
                $this->apiLog->makeTransactionRequestLog($logContext, $scopeId, $scopeType);
            }
        }

        return $result;
    }
}
