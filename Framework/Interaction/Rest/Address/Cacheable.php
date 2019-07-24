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

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest\Address;

use ClassyLlama\AvaTax\Api\RestAddressInterface;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address\Result as AddressResult;
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class Cacheable
 * @package ClassyLlama\AvaTax\Framework\Interaction\Rest\Address
 */
class Cacheable implements RestAddressInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var RestAddressInterface
     */
    protected $interactionAddress;

    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject
     */
    protected $metaDataObject;

    /**
     * @var PhpSerialize
     */
    private $phpSerialize;

    /**
     * Cacheable constructor.
     * @param PhpSerialize $phpSerialize
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestAddressInterface $interactionAddress
     * @param MetaDataObjectFactory $metaDataObjectFactory
     */
    public function __construct(
        PhpSerialize $phpSerialize,
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        RestAddressInterface $interactionAddress,
        MetaDataObjectFactory $metaDataObjectFactory
    ) {
        $this->phpSerialize = $phpSerialize;
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->interactionAddress = $interactionAddress;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \ClassyLlama\AvaTax\Framework\Interaction\Address::$validFields]
        );
    }

    /**
     * Cache validated response
     *
     * @param \Magento\Framework\DataObject $request
     * @param string|null                   $isProduction
     * @param string|int|null               $scopeId
     * @param string                        $scopeType
     *
     * @return AddressResult
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws AvataxConnectionException
     * @throws AddressValidateException
     * @throws \Exception
     */
    public function validate( $request, $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $addressCacheKey = $this->getCacheKey($request->getAddress()) . $scopeId;
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
        if ($validateResult instanceof AddressResult) {
            $this->avaTaxLogger->addDebug('Loaded address validate result from cache.', [
                'request' => var_export($request->getData(), true),
                'result' => var_export($validateResult->getData(), true),
                'cache_key' => $addressCacheKey
            ]);
            return $validateResult;
        } elseif (is_array($validateResult) && isset($validateResult['message']) && isset($validateResult['class'])) {
            $exceptionClass = $validateResult['class'];
            throw new $exceptionClass(__($validateResult['message']));
        }

        try {
            $validateResult = $this->interactionAddress->validate($request, null, $scopeId);
        } catch (AddressValidateException $e) {
            $exceptionData = [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ];
            $serializedException = $this->phpSerialize->serialize($exceptionData);
            $this->cache->save($serializedException, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        $serializedValidateResult = $this->phpSerialize->serialize($validateResult);
        $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);

        if ($validateResult->hasValidatedAddresses() && is_array($validateResult->getValidatedAddresses())) {
            $this->avaTaxLogger->addDebug('Loaded address validate result from REST.', [
                'request' => var_export($request->getData(), true),
                'result' => var_export($validateResult->getData(), true)
            ]);
        }

        return $validateResult;
    }

    /**
     * @inheritdoc
     */
    public function getClient( $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->interactionAddress->getClient( $isProduction, $scopeId, $scopeType);
    }

    /**
     * @inheritdoc
     */
    public function ping( $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->interactionAddress->ping( $isProduction, $scopeId, $scopeType);
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
     * Pass all undefined method calls through to REST address instance
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        return call_user_func_array([$this->interactionAddress, $name], $arguments);
    }
}
