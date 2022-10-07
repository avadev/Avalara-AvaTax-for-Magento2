<?php

namespace ClassyLlama\AvaTax\Api;

/**
 * Interface CertificatesInterface
 * @package ClassyLlama\AvaTax\Api
 */
interface CertificatesInterface
{

    /**
     * Get certificates list
     *
     * @param int|null $userId
     * @return array
     */
    public function getCertificatesList(int $userId = null): array;
}
