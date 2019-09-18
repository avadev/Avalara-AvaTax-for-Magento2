<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Storage;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Storage\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 * @package ClassyLlama\AvaTax\Framework\Interaction\Storage
 */
class Config implements ConfigInterface
{

    /**
     * @var string
     */
    const RESULT_CACHE_TTL = 'tax/avatax_advanced/result_cache_ttl';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get result cache ttl
     * Returns time in minutes.
     *
     * @return int
     */
    public function getResultCacheTtl(): int
    {
        return (int)$this->scopeConfig->getValue(self::RESULT_CACHE_TTL);
    }
}
