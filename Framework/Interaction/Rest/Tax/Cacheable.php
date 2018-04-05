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
use Magento\Framework\Phrase;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as TaxResult;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

class Cacheable implements \ClassyLlama\AvaTax\Api\RestTaxInterface
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
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestTaxInterface $taxInteraction
     * @param MetaDataObjectFactory $metaDataObjectFactory
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        RestTaxInterface $taxInteraction,
        MetaDataObjectFactory $metaDataObjectFactory
    ) {
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
        $forceNew = false;
        if (isset($params[\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES])) {
            $forceNew = $params[\ClassyLlama\AvaTax\Api\RestTaxInterface::FLAG_FORCE_NEW_RATES];
        }

        $cacheKey = $this->getCacheKey($request) . $scopeId;
        $getTaxResult = @unserialize($this->cache->load($cacheKey));

        if ($getTaxResult instanceof TaxResult && !$forceNew) {
            $this->avaTaxLogger->addDebug('Loaded tax result from cache.', [
                'result' => var_export($getTaxResult->getData(), true),
                'cache_key' => $cacheKey
            ]);
            return $getTaxResult;
        }

        $getTaxResult = $this->taxInteraction->getTax($request, null, $scopeId);
        if (!($getTaxResult instanceof TaxResult)) {
            throw new LocalizedException(__('Bad response from AvaTax'));
        }

        $this->avaTaxLogger->addDebug('Loaded tax result from REST.', [
            'request' => var_export($request->getData(), true),
            'result' => var_export($getTaxResult->getData(), true),
        ]);

        // Only cache successful requests
        if (!$forceNew) {
            $serializedGetTaxResult = serialize($getTaxResult);
            $this->cache->save(
                $serializedGetTaxResult,
                $cacheKey,
                [Config::AVATAX_CACHE_TAG],
                self::CACHE_LIFETIME
            );
        }
        return $getTaxResult;
    }

    /**
     * @inheritdoc
     */
    public function getClient($mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->taxInteraction->getClient($mode, $scopeId, $scopeType);
    }

    /**
     * @inheritdoc
     */
    public function ping($mode = null, $scopeId = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->taxInteraction->ping($mode, $scopeId, $scopeType);
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
