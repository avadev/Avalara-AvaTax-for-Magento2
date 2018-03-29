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

use ClassyLlama\AvaTax\Helper\Config;
use Avalara\AvaTaxClientFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory as TaxResultFactory;
use Avalara\TransactionBuilderFactory;

class Tax extends \ClassyLlama\AvaTax\Framework\Interaction\Rest
{
    /**
     * @var TransactionBuilderFactory
     */
    protected $transactionBuilderFactory;

    /**
     * @var TaxResultFactory
     */
    protected $taxResultFactory;

    /**
     * @param Config $config
     * @param AvaTaxClientFactory $avaTaxClientFactory
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param TransactionBuilderFactory $transactionBuilderFactory
     * @param TaxResultFactory $taxResultFactory
     */
    public function __construct(
        Config $config,
        AvaTaxClientFactory $avaTaxClientFactory,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        TransactionBuilderFactory $transactionBuilderFactory,
        TaxResultFactory $taxResultFactory
    ) {
        parent::__construct($config, $avaTaxClientFactory, $logger, $dataObjectFactory);
        $this->transactionBuilderFactory = $transactionBuilderFactory;
        $this->taxResultFactory = $taxResultFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param null|string $mode
     * @param null|string|int $scopeId
     * @param string $scopeType
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTax($request, $mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $client = $this->getClient($mode, $scopeId, $scopeType);

        /** @var \Avalara\TransactionBuilder $transactionBuilder */
        $transactionBuilder = $this->transactionBuilderFactory->create([
            'client' => $client,
            'companyCode' => 'CLASSYINC', // TODO: Get this dynamically
            'type' => \Avalara\DocumentType::C_SALESORDER,
            'customerCode' => 'My Customer', // TODO: Get this dynamically
        ]);

        // TODO: Replace with real details
        $transactionBuilder->withAddress('SingleLocation', '2920 Zoo Dr', NULL, NULL, 'San Diego', 'CA', '92101', 'US');
        if ($request->hasLines()) {
            $lineNumber = 1;
            foreach ($request->getLines() as $line) {
                $transactionBuilder->withLine(100.0, 1, 'code-' . $lineNumber, 'P0000000');
                /**
                 * It's only here that we can set the line number on the request items, when we're sure it will be the same as the line number in the response
                 */
                $line->setLineNumber($lineNumber);

                $lineNumber++;
            }
        }
        $resultObj = $transactionBuilder->create();

        $this->validateResult($resultObj);

        $resultGeneric = $this->formatResult($resultObj);
        /** @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result $result */
        $result = $this->taxResultFactory->create(['data' => $resultGeneric->getData()]);

        /**
         * We store the request on the result so we can map request items to response items
         */
        $result->setRequest($request);

        return $result;
    }
}