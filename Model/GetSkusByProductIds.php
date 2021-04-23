<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides all product SKUs by ProductIds. Key is product id, value is sku
 */
class GetSkusByProductIds
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @param ProductResourceModel $productResource
     */
    public function __construct(
        ProductResourceModel $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @param array $productIds
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(array $productIds)
    {
        $skuByIds = array_column(
            $this->productResource->getProductsSku($productIds),
            ProductInterface::SKU,
            'entity_id'
        );
        $notFoundedIds = array_diff($productIds, array_keys($skuByIds));

        if (!empty($notFoundedIds)) {
            throw new NoSuchEntityException(
                __('Following products with requested ids were not found: %1', implode($notFoundedIds, ', '))
            );
        }

        $skuByIds = array_map('strval', $skuByIds);
        return $skuByIds;
    }
}
