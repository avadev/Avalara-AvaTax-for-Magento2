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

use ClassyLlama\AvaTax\Framework\Interaction\Rest\Address as RestAddressInteraction;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

// TODO: Consider making this and Rest/Address implement the same interface?
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
     * @var RestAddressInteraction
     */
    protected $interactionAddress = null;

    protected $type = null;

    /**
     * @param CacheInterface $cache
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestAddressInteraction $interactionAddress
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param null $type
     */
    public function __construct(
        CacheInterface $cache,
        AvaTaxLogger $avaTaxLogger,
        RestAddressInteraction $interactionAddress,
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
     * @param \Magento\Framework\DataObject $validateRequest
     * @param int $storeId
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     */
    public function validate($validateRequest, $storeId)
    {
        $addressCacheKey = $this->getCacheKey($validateRequest->getAddress()) . $storeId;
        $validateResult = @unserialize($this->cache->load($addressCacheKey));

        if ($validateResult instanceof DataObject) {
            $this->avaTaxLogger->addDebug('Loaded address validate result from cache.', [
                'request' => var_export($validateRequest->getData(), true),
                'result' => var_export($validateResult->getData(), true),
                'cache_key' => $addressCacheKey
            ]);
            return $validateResult;
        }

        $validateResult = $this->interactionAddress->validate($validateRequest, null, $storeId);

        $serializedValidateResult = serialize($validateResult);
        $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVATAX_CACHE_TAG]);

        if ($validateResult->hasValidatedAddresses() && is_array($validateResult->getValidatedAddresses())) {
            $this->avaTaxLogger->addDebug('Loaded address validate result from REST.', [
                'request' => var_export($validateRequest->getData(), true),
                'result' => var_export($validateResult->getData(), true)
            ]);
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
}
