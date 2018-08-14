<?php
namespace ClassyLlama\AvaTax\Model\CrossBorderClass;

/**
 * Factory class for @see \ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManager
 */
class ProductsManagerFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var \ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManager[]
     */
    protected $productManagers = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManager
     */
    public function create(array $data = array())
    {
        $cacheKey = $this->getCacheKey($data);

        if (!isset($this->productManagers[$cacheKey])) {
            $this->productManagers[$cacheKey] = $this->objectManager->create(\ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManager::class, $data);
        }

        return $this->productManagers[$cacheKey];
    }

    /**
     * Get unique cache key for a particular manager
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $country = (isset($data['destinationCountry'])) ? $data['destinationCountry'] : '';

        $productCrossBorderTypes = [];
        if (isset($data['productCrossBorderTypes'])) {
            ksort($data['productCrossBorderTypes']);

            foreach ($data['productCrossBorderTypes'] as $productId => $crossBorderType) {
                $productCrossBorderTypes[] = (string) $productId . '-' . (string) $crossBorderType;
            }
        }

        $cacheParts = [
            $country,
            implode('|', $productCrossBorderTypes),
        ];

        $cacheString = implode('||', $cacheParts);

        return hash('sha256', $cacheString);
    }
}
