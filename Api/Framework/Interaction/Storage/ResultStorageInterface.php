<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Storage;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use Magento\Framework\DataObject;
use Zend\Serializer\Exception\RuntimeException;

/**
 * Interface ResultStorageInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Storage
 */
interface ResultStorageInterface
{

    /**
     * Find result by request in storage
     *
     * @param RequestInterface $request
     * @return DataObject|null
     */
    public function find(RequestInterface $request);

    /**
     * Insert result to storage
     *
     * @param RequestInterface $request
     * @param DataObject $result
     * @return ResultStorageInterface
     */
    public function insert(RequestInterface $request, DataObject $result): ResultStorageInterface;

    /**
     * Invalidate cache storage
     * Remove all expired results from cache
     *
     * @return ResultStorageInterface
     */
    public function invalidate(): ResultStorageInterface;

    /**
     * Get cache key of request
     *
     * @param RequestInterface $request
     * @return string
     * @throws RuntimeException
     */
    public function generateCacheKey(RequestInterface $request): string;
}
