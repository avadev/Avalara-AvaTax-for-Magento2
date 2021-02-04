<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\TaxCompositeInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\ClientPool;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax as InteractionRestTax;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as RestTaxResult;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\ResultFactory as TaxResultFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Model\Factory\TransactionBuilderFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Storage\ResultStorage;
use Psr\Log\LoggerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Request\Request as CreditmemoRequest;

/**
 * Class TaxComposite
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 */
class TaxComposite extends InteractionRestTax implements TaxCompositeInterface
{

    /**
     * @var ResultStorage
     */
    private $resultStorage;

    /**
     * TaxComposite constructor.
     *
     * @param ResultStorage $resultStorage
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param TransactionBuilderFactory $transactionBuilderFactory
     * @param TaxResultFactory $taxResultFactory
     * @param RestConfig $restConfig
     * @param CustomsConfig $customsConfigHelper
     * @param Config $config
     */
    public function __construct(
        ResultStorage $resultStorage,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        TransactionBuilderFactory $transactionBuilderFactory,
        TaxResultFactory $taxResultFactory,
        RestConfig $restConfig,
        CustomsConfig $customsConfigHelper,
        Config $config
    ) {
        parent::__construct(
            $logger,
            $dataObjectFactory,
            $clientPool,
            $transactionBuilderFactory,
            $taxResultFactory,
            $restConfig,
            $customsConfigHelper,
            $config
        );
        $this->resultStorage = $resultStorage;
    }

    /**
     * Calculate tax
     *
     * @param RequestInterface $request
     * @param int $storeId
     * @param string $scopeType
     * @param array $params
     * @param bool|null $isProduction
     * @return RestTaxResult
     * @throws LocalizedException
     * @throws AvataxConnectionException
     * @throws \Exception
     */
    public function calculateTax(RequestInterface $request, int $storeId, string $scopeType, array $params = [], $isProduction = null): RestTaxResult
    {
        /**
         * @var CreditmemoRequest $request
         * @var RequestInterface $cacheKey
         */
        $cacheKey = clone $request;

        /**
         * As "code" field is a unique value for each Request object, a checksum for the same object will be different
         * To avoid this, we have to eliminate "code" field before checking Request object checksum
         * @var string|null $code
         * @var DataObject $cacheKey
         */
        $cacheKey->hasData('code') ? $cacheKey->unsetData('code') : null;

        /** @var RestTaxResult|null $taxes */
        $taxes = $this->resultStorage->find($cacheKey);

        if (null !== $taxes) {
            return $taxes;
        }

        /**
         * @var RestTaxResult $taxes
         * @var DataObject $request
         */
        $result = $this->getTax($request, $isProduction, $storeId, $scopeType, $params);
        $this->resultStorage->insert($cacheKey, $result);

        return $result;
    }
}
