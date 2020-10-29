<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Storage;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Storage\ResultStorageInterface;
use Magento\Framework\DataObject;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Storage\ConfigInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Storage\Session as SessionStorage;
use Zend\Serializer\Exception\RuntimeException;
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class ResultStorage
 * @package ClassyLlama\AvaTax\Framework\Interaction\Storage
 */
class ResultStorage implements ResultStorageInterface
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    private $results;

    /**
     * @var SessionStorage
     */
    protected $session;

    /**
     * @var PhpSerialize
     */
    private $phpSerialize;

    /**
     * ResultStorage constructor.
     * @param string $namespace
     * @param Session $session
     * @param ConfigInterface $config
     * @param PhpSerialize $phpSerialize
     */
    public function __construct(
        string $namespace,
        SessionStorage $session,
        ConfigInterface $config,
        PhpSerialize $phpSerialize
    ) {
        $this->namespace = $namespace;
        $this->config = $config;
        $this->results = $session->getResults($this->namespace);
        $this->invalidate();
        $this->session = $session;
        $this->phpSerialize = $phpSerialize;
    }

    /**
     * Find result by request in storage
     *
     * @param RequestInterface $request
     * @return DataObject|null
     */
    public function find(RequestInterface $request)
    {
        /** @var DataObject|null $result */
        $result = $this->results[$this->generateCacheKey($request)] ?? null;

        if (null !== $result) {
            $result->setData('cache', true);
        }

        return $result;
    }

    /**
     * Insert result to storage
     *
     * @param RequestInterface $request
     * @param DataObject $result
     * @return ResultStorageInterface
     */
    public function insert(RequestInterface $request, DataObject $result): ResultStorageInterface
    {
        try {
            /** @var int $timestamp */
            $timestamp = (int)(new \DateTime())->getTimestamp();
        } catch (\Exception $exception) {
            $timestamp = null;
        }
        $result->setData('creation_timestamp', $timestamp);
        $this->results[$this->generateCacheKey($request)] = $result;
        $this->persist();

        return $this;
    }

    /**
     * Invalidate cache storage
     * Remove all expired results from cache
     *
     * @return ResultStorageInterface
     */
    public function invalidate(): ResultStorageInterface
    {
        foreach ($this->results as $key => $result) {
            if ($this->isExpiredResult($result)) {
                unset($this->results[$key]);
            }
        }

        return $this;
    }

    /**
     * Checks whether result expired or not
     *
     * @param DataObject $result
     * @return bool
     */
    private function isExpiredResult(DataObject $result): bool
    {
        return (int)$result->getData('creation_timestamp') < $this->getExpirationTimestamp();
    }

    /**
     * Get cache key of request
     *
     * @param RequestInterface $request
     * @return string
     * @throws RuntimeException
     */
    public function generateCacheKey(RequestInterface $request): string
    {
        try {
            return (string)sprintf('%u', crc32($this->phpSerialize->serialize($request)));
        } catch (\Throwable $exception) {
            return '';
        }
    }

    /**
     * Persist data
     * Save results to session storage
     *
     * @return ResultStorageInterface
     */
    private function persist(): ResultStorageInterface
    {
        $this->session->setResults($this->namespace, $this->results);
        return $this;
    }

    /**
     * Get expiration timestamp
     *
     * @return int|null
     */
    private function getExpirationTimestamp()
    {
        try {
            return (int)(new \DateTime())
                ->modify(sprintf('-%s minutes', $this->config->getResultCacheTtl()))
                ->getTimestamp();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
