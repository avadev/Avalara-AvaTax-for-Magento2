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

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax;

use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as TaxResult;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class Cacheable
 * @package ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax
 */
class Cacheable implements RestTaxInterface
{
    /**
     * 1 day in seconds
     */
    const CACHE_LIFETIME = 86400;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Tax
     */
    protected $taxInteraction;

    /**
     * @var MetaDataObject
     */
    protected $metaDataObject = null;

    /**
     * @var PhpSerialize
     */
    private $phpSerialize;

    /**
     * Cacheable constructor.
     * @param PhpSerialize $phpSerialize
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestTaxInterface $taxInteraction
     * @param MetaDataObjectFactory $metaDataObjectFactory
     */
    public function __construct(
        PhpSerialize $phpSerialize,
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        RestTaxInterface $taxInteraction,
        MetaDataObjectFactory $metaDataObjectFactory
    ) {
        $this->phpSerialize = $phpSerialize;
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->taxInteraction = $taxInteraction;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \ClassyLlama\AvaTax\Framework\Interaction\Tax::$validFields]
        );
    }

    /**
     * Cache validated response
     *
     * @param \Magento\Framework\DataObject $request
     * @param null|string                   $isProduction
     * @param null|string|int               $scopeId
     * @param string                        $scopeType
     * @param array                         $params
     *
     * @return \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws AvataxConnectionException
     * @throws \Exception
     */
    public function getTax( $request, $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $params = [])
    {
        $forceNew = false;
        if (isset($params[\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES])) {
            $forceNew = $params[\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES];
        }

        $cacheKey = $this->getCacheKey($request) . $scopeId;
        $cacheData = $this->cache->load($cacheKey);
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
            $getTaxResult = !empty($cacheData) ? $this->phpSerialize->unserialize($cacheData) : '';
        } catch (\Throwable $exception) {
            $getTaxResult = '';
        }
        if ($getTaxResult instanceof TaxResult && !$forceNew) {
            $getTaxResultData = $getTaxResult->getData('raw_result');
            $getTaxRequestData = $getTaxResult->getData('raw_request');

            $this->avaTaxLogger->addDebug('Loaded tax result from cache.', [
                'cache_key' => $cacheKey,
                'request' => json_encode($getTaxRequestData, JSON_PRETTY_PRINT),
                'result' => json_encode($getTaxResultData, JSON_PRETTY_PRINT)
            ]);
            return $getTaxResult;
        }

        $getTaxResult = $this->taxInteraction->getTax($request, null, $scopeId);

        if (!($getTaxResult instanceof TaxResult)) {
            throw new LocalizedException(__('Bad response from AvaTax'));
        }

        // Only cache successful requests
        if (!$forceNew) {
            try {
                $serializedGetTaxResult = $this->phpSerialize->serialize($getTaxResult);
            } catch (\Throwable $exception) {
                $serializedGetTaxResult = '';
            }
            $this->cache->save(
                $serializedGetTaxResult,
                $cacheKey,
                [Config::AVATAX_CACHE_TAG, Config::AVATAX_CACHE_TAG . '-' . $request->getData('customer_code')],
                self::CACHE_LIFETIME
            );
        }
        return $getTaxResult;
    }

    /**
     * @inheritdoc
     */
    public function getClient( $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->taxInteraction->getClient( $isProduction, $scopeId, $scopeType);
    }

    /**
     * @inheritdoc
     */
    public function ping( $isProduction = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->taxInteraction->ping( $isProduction, $scopeId, $scopeType);
    }

    /**
     * Create cache key by calling specified methods and concatenating and hashing
     *
     * @param $object
     * @return string
     */
    protected function getCacheKey($object)
    {
        return $this->metaDataObject->getCacheKeyFromObject($object);
    }

    /**
     * Pass all undefined method calls through to REST tax instance
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        return call_user_func_array([$this->taxInteraction, $name], $arguments);
    }
}
