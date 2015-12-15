<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Cacheable;

use AvaTax\ValidateRequest;
use AvaTax\ValidateResult;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AddressService
{
    /**
     * Properties on object to use as cache key
     *
     * @var array
     */
    protected $cacheFields = ['Line1', 'Line2', 'Line3', 'City', 'Region', 'PostalCode', 'Country'];

    /**
     * @var CacheInterface
     */
    protected $cache = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger = null;

    /**
     * @var Address
     */
    protected $interactionAddress = null;

    /**
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param Address $interactionAddress
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        Address $interactionAddress
    ) {
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->interactionAddress = $interactionAddress;
    }

    /**
     * Cache validated response
     * TODO: Create cache clear item in admin
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param ValidateRequest $validateRequest
     * @return ValidateResult
     * @throws LocalizedException
     */
    public function validate(ValidateRequest $validateRequest)
    {
        $addressCacheKey = $this->getCacheKey($validateRequest->getAddress());
        $validateResult = @unserialize($this->cache->load($addressCacheKey));

        if ($validateResult instanceof ValidateResult) {
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\ValidateResult from cache.', ['result' => $validateResult, 'cache_key' => $addressCacheKey]);
            return $validateResult;
        }

        $validateResult = $this->interactionAddress->getAddressService()->validate($validateRequest);
        $validAddressCacheKey = $this->getCacheKey($this->getCacheKey($validateResult->getValidAddresses()[0]));
        $this->avaTaxLogger->addDebug('Loaded \AvaTax\ValidateResult from SOAP.', ['result' => $validateResult]);

        $serializedValidateResult = serialize($validateResult);
        $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);
        $this->cache->save($serializedValidateResult, $validAddressCacheKey, [Config::AVATAX_CACHE_TAG]);
        return $validateResult;
    }

    /**
     * Create cache key by calling specified methods and concatenating and hashing
     * TODO: Get Anya to update to create a new tag off of master so that getCountry on \AvaTax\Address works correctly
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