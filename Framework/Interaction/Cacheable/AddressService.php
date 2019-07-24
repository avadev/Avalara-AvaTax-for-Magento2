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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

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
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class AddressService
 * @package ClassyLlama\AvaTax\Framework\Interaction\Cacheable
 */
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
     * @var PhpSerialize
     */
    protected $phpSerialize;

    /**
     * AddressService constructor.
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param Address $interactionAddress
     * @param PhpSerialize $phpSerialize
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param null $type
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        Address $interactionAddress,
        PhpSerialize $phpSerialize,
        MetaDataObjectFactory $metaDataObjectFactory,
        $type = null
    ) {
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->interactionAddress = $interactionAddress;
        $this->phpSerialize = $phpSerialize;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \ClassyLlama\AvaTax\Framework\Interaction\Address::$validFields]
        );
        $this->type = $type;
    }

    /**
     * Cache validated response
     *
     * @param ValidateRequest $validateRequest
     * @param $storeId
     * @return ValidateResult
     * @throws \SoapFault
     */
    public function validate(ValidateRequest $validateRequest, $storeId)
    {
        $addressCacheKey = $this->getCacheKey($validateRequest->getAddress()) . $storeId;
        $cacheData = $this->cache->load($addressCacheKey);
        try {
            /**
             * Magento 2.2.x, 2.3.x
             * - we can not use \Magento\Framework\Serialize\Serializer\Serialize::unserialize. Magento realization does
             *   not allow us to control 'allowed_classes' restriction option of unserialize().
             * - we can not use native PHP serialize() and unserialize() in our own realization, because it won't pass
             *   Magento Coding Standard.
             * Magento 2.1.x
             * - \Magento\Framework\Serialize\Serializer\Serialize - class is absent
             * Was chosen \Zend\Serializer\Adapter\PhpSerialize::unserialize. It exists in 2.1.x - 2.3.x
             * It allows us to configure 'allowed_classes' restriction option (Magento 2.2.x, 2.3.x)
             */
            $validateResult = !empty($cacheData) ? $this->phpSerialize->unserialize($cacheData) : '';
        } catch (\Throwable $exception) {
            $validateResult = '';
        }
        if ($validateResult instanceof ValidateResult) {
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\ValidateResult from cache.', [
                'request' => var_export($validateRequest, true),
                'result' => var_export($validateResult, true),
                'cache_key' => $addressCacheKey
            ]);
            return $validateResult;
        }

        try {
            $addressService = $this->interactionAddress->getAddressService($this->type, $storeId);
            $validateResult = $addressService->validate($validateRequest);

            $serializedValidateResult = $this->phpSerialize->serialize($validateResult);
            $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);

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
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        return call_user_func_array([$this->interactionAddress->getAddressService($this->type), $name], $arguments);
    }
}
