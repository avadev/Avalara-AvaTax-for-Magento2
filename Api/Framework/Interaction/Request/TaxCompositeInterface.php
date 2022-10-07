<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as RestTaxResult;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

/**
 * Interface TaxCompositeInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Request
 */
interface TaxCompositeInterface extends RestTaxInterface
{

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
    public function calculateTax(RequestInterface $request, int $storeId, string $scopeType, array $params, $isProduction): RestTaxResult;
}
