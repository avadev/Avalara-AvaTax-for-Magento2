<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Storage;

use Magento\Framework\Session\SessionManager;

/**
 * Class Session
 * @property \Magento\Framework\Session\Storage $storage
 * @package ClassyLlama\AvaTax\Framework\Interaction\Storage
 */
class Session extends SessionManager
{

    /**
     * Get results by namespace
     *
     * @param string $namespace
     * @return array
     */
    public function getResults(string $namespace = ''): array
    {
        if (!empty($namespace)) {
            return (array)$this->storage->getData($namespace);
        }
        return [];
    }

    /**
     * Set results
     *
     * @param string $namespace
     * @param array $results
     * @return Session
     */
    public function setResults(string $namespace = '', array $results = []): self
    {
        if (!empty($namespace)) {
            $this->storage->setData($namespace, $results);
        }
        return $this;
    }
}
