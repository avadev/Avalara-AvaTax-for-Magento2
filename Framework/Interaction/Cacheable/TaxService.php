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

use AvaTax\GetTaxRequest;
use AvaTax\GetTaxResult;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class TaxService
 * @package ClassyLlama\AvaTax\Framework\Interaction\Cacheable
 */
class TaxService
{
    /**
     * 1 day in seconds
     */
    const CACHE_LIFETIME = 86400;

    /**
     * @var CacheInterface
     */
    protected $cache = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger = null;

    /**
     * @var Tax
     */
    protected $taxInteraction = null;

    /**
     * @var MetaDataObject
     */
    protected $metaDataObject = null;

    /**
     * @var null
     */
    protected $type = null;

    /**
     * @var PhpSerialize
     */
    protected $phpSerialize;

    /**
     * TaxService constructor.
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param Tax $taxInteraction
     * @param PhpSerialize $phpSerialize
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param null $type
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        Tax $taxInteraction,
        PhpSerialize $phpSerialize,
        MetaDataObjectFactory $metaDataObjectFactory,
        $type = null
    ) {
        $this->cache = $cache;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->taxInteraction = $taxInteraction;
        $this->phpSerialize = $phpSerialize;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \ClassyLlama\AvaTax\Framework\Interaction\Tax::$validFields]
        );
    }

    /**
     * Cache validated response
     *
     * @param GetTaxRequest $getTaxRequest
     * @param $storeId
     * @param bool $useCache
     * @return GetTaxResult
     * @throws LocalizedException
     */
    public function getTax(GetTaxRequest $getTaxRequest, $storeId, $useCache = false)
    {
        $cacheKey = $this->getCacheKey($getTaxRequest) . $storeId;
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
        if ($getTaxResult instanceof GetTaxResult && $useCache) {
            $this->avaTaxLogger->addDebug('Loaded \AvaTax\GetTaxResult from cache.', [
                'result' => var_export($getTaxResult, true),
                'cache_key' => $cacheKey
            ]);
            return $getTaxResult;
        }

        $getTaxResult = $this->taxInteraction->getTaxService($this->type, $storeId)->getTax($getTaxRequest);
        $this->avaTaxLogger->addDebug('Loaded \AvaTax\GetTaxResult from SOAP.', [
            'request' => var_export($getTaxRequest, true),
            'result' => var_export($getTaxResult, true),
        ]);

        // Only cache successful requests
        if ($useCache && $getTaxResult->getResultCode() == \AvaTax\SeverityLevel::$Success) {
            $serializedGetTaxResult = $this->phpSerialize->serialize($getTaxResult);
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
     * Pass all undefined method calls through to Tax Service
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        return call_user_func_array([$this->taxInteraction->getTaxService($this->type), $name], $arguments);
    }

}
