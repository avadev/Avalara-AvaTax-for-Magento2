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

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory as TaxResultFactory;
use Avalara\TransactionBuilderFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

class Tax extends \ClassyLlama\AvaTax\Framework\Interaction\Rest
    implements \ClassyLlama\AvaTax\Api\RestTaxInterface
{
    const FLAG_FORCE_NEW_RATES = 'force_new_rates';

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
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param TransactionBuilderFactory $transactionBuilderFactory
     * @param TaxResultFactory $taxResultFactory
     * @param RestConfig $restConfig
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        TransactionBuilderFactory $transactionBuilderFactory,
        TaxResultFactory $taxResultFactory,
        RestConfig $restConfig
    ) {
        parent::__construct($logger, $dataObjectFactory, $clientPool);
        $this->transactionBuilderFactory = $transactionBuilderFactory;
        $this->taxResultFactory = $taxResultFactory;
        $this->restConfig = $restConfig;
    }

    /**
     * REST call to post tax transaction
     *
     * @param \Magento\Framework\DataObject $request
     * @param null|string $mode
     * @param null|string|int $scopeId
     * @param string $scopeType
     * @param array $params
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws AvataxConnectionException
     * @throws \Exception
     */
    public function getTax($request, $mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $params = [])
    {
        $client = $this->getClient($mode, $scopeId, $scopeType);

        /** @var \Avalara\TransactionBuilder $transactionBuilder */
        $transactionBuilder = $this->transactionBuilderFactory->create([
            'client' => $client,
            'companyCode' => $request->getCompanyCode(),
            'type' => $request->getType(),
            'customerCode' => $request->getCustomerCode(),
        ]);

        $this->setTransactionDetails($transactionBuilder, $request);
        $this->setLineDetails($transactionBuilder, $request);
        $this->setAddressDetails($transactionBuilder, $request);

        $resultObj = $transactionBuilder->create();
        $this->validateResult($resultObj, $request);

        $resultGeneric = $this->formatResult($resultObj);
        /** @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result $result */
        $result = $this->taxResultFactory->create(['data' => $resultGeneric->getData()]);

        /**
         * We store the request on the result so we can map request items to response items
         */
        $result->setRequest($request);

        return $result;
    }

    /**
     * Set transaction-level fields for request
     *
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Magento\Framework\DataObject $request
     */
    protected function setTransactionDetails($transactionBuilder, $request)
    {
        if ($request->getCommit()) {
            $transactionBuilder->withCommit();
        }
        if ($request->getIsSellerImporterOfRecord()) {
            $transactionBuilder->withSellerIsImporterOfRecord();
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
            $transactionBuilder->withExchangeRate($request->getExchangeRate(), $request->getExchangeRateEffectiveDate());
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
                $transactionBuilder->withTaxOverride($override->getType(), $override->getReason(), $override->getTaxAmount(), $override->getTaxDate());
            }
        }
    }

    /**
     * Set address entries and fields for request
     *
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Magento\Framework\DataObject $request
     * @throws \Exception
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

                /**
                 * It's only here that we can set the line number on the request items, when we're sure it will be the same as the line number in the response
                 */
                $line->setNumber($transactionBuilder->getMostRecentLineNumber());
            }
        }
    }

    /**
     * Set line item entries and fields for request
     *
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Magento\Framework\DataObject $request
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
}