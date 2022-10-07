<?php

namespace ClassyLlama\AvaTax\Api\Framework\Interaction\Storage;

/**
 * Interface ConfigInterface
 * @package ClassyLlama\AvaTax\Api\Framework\Interaction\Storage
 */
interface ConfigInterface
{

    /**
     * Get result cache ttl
     * Returns time in minutes.
     *
     * @return int
     */
    public function getResultCacheTtl(): int;
}
