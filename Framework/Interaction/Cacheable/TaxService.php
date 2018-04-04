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

// TODO: Consider making this and Rest/Tax implement the same interface?
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
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestTaxInterface $taxInteraction
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param null $type
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        RestTaxInterface $taxInteraction,
        MetaDataObjectFactory $metaDataObjectFactory,
        $type = null
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
     * @param \Magento\Framework\DataObject $getTaxRequest
     * @param $storeId
     * @param bool $useCache
     * @return TaxResult
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getTax($getTaxRequest, $storeId, $useCache = false)
    {
        $cacheKey = $this->getCacheKey($getTaxRequest) . $storeId;
        $getTaxResult = @unserialize($this->cache->load($cacheKey));

        if ($getTaxResult instanceof TaxResult && $useCache) {
            $this->avaTaxLogger->addDebug('Loaded tax result from cache.', [
                'result' => var_export($getTaxResult->getData(), true),
                'cache_key' => $cacheKey
            ]);
            return $getTaxResult;
        }

        $getTaxResult = $this->taxInteraction->getTax($getTaxRequest, null, $storeId);
        if (!($getTaxResult instanceof TaxResult)) {
            throw new LocalizedException(__('Bad response from AvaTax'));
        }

        $this->avaTaxLogger->addDebug('Loaded tax result from REST.', [
            'request' => var_export($getTaxRequest->getData(), true),
            'result' => var_export($getTaxResult->getData(), true),
        ]);

        // Only cache successful requests
        if ($useCache) {
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
     * Create cache key by calling specified methods and concatenating and hashing
     *
     * @param $object
     * @return string
     */
    protected function getCacheKey($object)
    {
        return $this->metaDataObject->getCacheKeyFromObject($object);
    }

}
