<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Cacheable;

use AvaTax\ValidateRequest;
use AvaTax\ValidateResult;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AddressService
{
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

    protected $type = null;

    /**
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param Address $interactionAddress
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param null $type
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        Address $interactionAddress,
        MetaDataObjectFactory $metaDataObjectFactory,
        $type = null
    ) {
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->interactionAddress = $interactionAddress;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \ClassyLlama\AvaTax\Framework\Interaction\Address::$validFields]
        );
        $this->type = $type;
    }

    /**
     * Cache validated response
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param ValidateRequest $validateRequest
     * @return ValidateResult
     * @throws \SoapFault
     */
    public function validate(ValidateRequest $validateRequest)
    {
        $addressCacheKey = $this->getCacheKey($validateRequest->getAddress());
        $validateResult = @unserialize($this->cache->load($addressCacheKey));

        if ($validateResult instanceof ValidateResult) {
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\ValidateResult from cache.', [
                'request' => var_export($validateRequest, true),
                'result' => var_export($validateResult, true),
                'cache_key' => $addressCacheKey
            ]);
            return $validateResult;
        }

        $addressService = $this->interactionAddress->getAddressService($this->type);
        $validateResult = $addressService->validate($validateRequest);

        $serializedValidateResult = serialize($validateResult);
        $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);

        try {
            $validAddress =
                isset($validateResult->getValidAddresses()[0]) ? $validateResult->getValidAddresses()[0] : null;
            $validAddressCacheKey = $this->getCacheKey($validAddress);
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\ValidateResult from SOAP.', [
                'request' => var_export($validateRequest, true),
                'result' => var_export($validateResult, true)
            ]);

            $this->cache->save($serializedValidateResult, $validAddressCacheKey, [Config::AVATAX_CACHE_TAG]);
        } catch (LocalizedException $e) {
            $this->avaTaxLogger->addDebug('\AvaTax\ValidateResult no valid address found from SOAP.', [
                'result' => var_export($validateResult, true)
            ]);
        } catch (\SoapFault $e) {
            $this->avaTaxLogger->error(
                "Exception: \n" . $e->getMessage() . "\n" . $e->faultstring,
                [
                    'request' => var_export($addressService->__getLastRequest(), true),
                    'result' => var_export($addressService->__getLastResponse(), true),
                ]
            );

            throw $e;
        }

        return $validateResult;
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
        return $this->metaDataObject->getCacheKeyFromObject($object);
    }

    /**
     * Pass all undefined method calls through to AddressService
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        return call_user_func_array([$this->interactionAddress->getAddressService($this->type), $name], $arguments);
    }
}