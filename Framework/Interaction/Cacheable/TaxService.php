<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Cacheable;

use AvaTax\GetTaxRequest;
use AvaTax\GetTaxResult;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class TaxService
{
    /**
     * Properties on object to use as cache key
     *
     * @var array
     */
    protected $cacheFields = [];

    /**
     * @var CacheInterface
     */
    protected $cache = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger = null;

    /**
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * Cache validated response
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param GetTaxRequest $getTaxRequest
     * @return GetTaxResult
     * @throws LocalizedException
     */
    public function getTax(GetTaxRequest $getTaxRequest)
    {

        $validDataFields = [
            'business_identification_no',
            'commit',
            // Company Code is not required by the the API, but we are requiring it in this integration
            'company_code',
            'currency_code',
            'customer_code',
            'customer_usage_type',
            'destination_address' => ['type' => 'object', 'class' => '\AvaTax\Address', 'required' => true],
            'detail_level',
            'discount',
            'doc_code',
            'doc_date',
            'doc_type',
            'exchange_rate',
            'exchange_rate_eff_date',
            'exemption_no',
            'lines' => [
                'type' => 'array',
                'length' => 15000,
                'subtype' => ['*' => ['type' => 'object', 'class' => '\AvaTax\Line']],
                'required' => true,
            ],
            'location_code',
            'origin_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
            'payment_date',
            'purchase_order_number',
            'reference_code',
            'salesperson_code',
            'tax_override' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
        ];

        $cacheKey = $this->getCacheKey($getTaxRequest->getAddress());
        $getTaxResult = @unserialize($this->cache->load($cacheKey));

        if ($getTaxResult instanceof GetTaxResult) {
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\GetTaxResult from cache.', ['result' => $getTaxResult, 'cache_key' => $cacheKey]);
            return $getTaxResult;
        }

        $getTaxResult = $this->interactionAddress->getAddressService()->validate($getTaxRequest);
        $this->avaTaxLogger->addDebug('Loaded \AvaTax\GetTaxResult from SOAP.', ['result' => $getTaxResult]);

        $serializedGetTaxResult = serialize($getTaxResult);
        $this->cache->save($serializedGetTaxResult, $cacheKey, [Config::AVATAX_CACHE_TAG]);
        return $getTaxResult;
    }

    /**
     * Create cache key by calling specified methods and concatenating and hashing
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $object
     * @return string
     * @throws LocalizedException
     */
    protected function getCacheKey($object)
    {
        $cacheKey = '';
        foreach ($this->cacheFields as $field) {
            $methodName = 'get' . $field;
            if (method_exists($object, $methodName)) {
                $cacheKey .= call_user_func([$object, $methodName]);
            } else {
                throw new LocalizedException(
                    new Phrase('The method for the passed in field "%1" could not be found.', [$field])
                );
            }
        }
    }
}