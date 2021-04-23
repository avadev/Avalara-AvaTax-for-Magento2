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

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class TaxClass
 */
class TaxClass
{
    /**
     * Avatax gift certificate tax code
     */
    const GIFT_CARD_LINE_AVATAX_TAX_CODE = 'PG050000';

    /**
     * UPC Format
     */
    const UPC_FORMAT = 'UPC: %s';

    /**
     * Type code for Gift Card (@see \Magento\GiftCard\Model\Catalog\Product\Type\Giftcard::TYPE_GIFTCARD)
     */
    const PRODUCT_TYPE_GIFTCARD = 'giftcard';

    /**
     * Gift wrapping tax class
     *
     * Copied from \Magento\GiftWrapping\Helper\Data since it is an Enterprise-only module
     */
    const XML_PATH_TAX_CLASS_GIFT_WRAPPING = 'tax/classes/wrapping_tax_class';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \ClassyLlama\AvaTax\Model\GetSkusByProductIds
     */
    private $getSkusByProductIds;

    /**
     * Class constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \ClassyLlama\AvaTax\Helper\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \ClassyLlama\AvaTax\Model\GetSkusByProductIds $getSkusByProductIds
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \ClassyLlama\AvaTax\Helper\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \ClassyLlama\AvaTax\Model\GetSkusByProductIds $getSkusByProductIds
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->taxClassRepository = $taxClassRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->config = $config;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * Get the AvaTax Tax Code for a product
     *
     * @param int $taxClassId
     * @return string|null
     */
    protected function getAvaTaxTaxCode($taxClassId)
    {
        try {
            $taxClass = $this->taxClassRepository->get($taxClassId);
            return $taxClass->getAvataxCode();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get AvaTax Tax Code for a product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $storeId
     * @return null|string
     */
    public function getAvataxTaxCodeForProduct(\Magento\Catalog\Model\Product $product, $storeId)
    {
        if ($product->getTypeId() == self::PRODUCT_TYPE_GIFTCARD) {
            return self::GIFT_CARD_LINE_AVATAX_TAX_CODE;
        } else {
            try {
                $itemSkuArray = $this->getSkusByProductIds->execute(
                    [$product->getId()]
                );

                $itemSku = $itemSkuArray[$product->getId()] ?? $product->getSku();
                $simpleProduct = $this->productRepository->get($itemSku);
            } catch (\Throwable $e) {
                $simpleProduct = $product;
            }

            return $this->getAvaTaxTaxCode($simpleProduct->getTaxClassId() ?: $product->getTaxClassId());
        }
    }

    /**
     * Get AvaTax ItemCode for product if UPC is configured and product contains UPC
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null|string
     */
    public function getItemCodeOverride(\Magento\Catalog\Model\Product $product)
    {
        if ($this->config->getUpcAttribute() && $product->getData($this->config->getUpcAttribute())) {
            $upcCode = $product->getData($this->config->getUpcAttribute());
            if ($upcCode) {
                return sprintf(self::UPC_FORMAT, $upcCode);
            }
        }
        return null;
    }

    /**
     * Get Ref1 code for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed|null
     */
    public function getRef1ForProduct(\Magento\Catalog\Model\Product $product)
    {
        if ($this->config->getRef1Attribute() && $product->getData($this->config->getRef1Attribute())) {
            return $product->getData($this->config->getRef1Attribute());
        }
        return null;
    }

    /**
     * Get Ref2 code for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed|null
     */
    public function getRef2ForProduct(\Magento\Catalog\Model\Product $product)
    {
        if ($this->config->getRef2Attribute() && $product->getData($this->config->getRef2Attribute())) {
            return $product->getData($this->config->getRef2Attribute());
        }
        return null;
    }

    /**
     * Get AvaTax Tax Code for shipping
     * Default Configuration Setting: FR020100
     *
     * @return string
     */
    public function getAvataxTaxCodeForShipping()
    {
        return $this->config->getShippingTaxCode();
    }

    /**
     * @param $store
     * @return null|string
     */
    public function getAvataxTaxCodeForGiftOptions($store)
    {
        $taxClassId = $this->scopeConfig->getValue(
            self::XML_PATH_TAX_CLASS_GIFT_WRAPPING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $this->getAvaTaxTaxCode($taxClassId);
    }

    /**
     * Get AvaTax Customer Usage Type for customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return null|string
     */
    public function getAvataxTaxCodeForCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerGroupId = $customer->getGroupId();
        if (!$customerGroupId) {
            return null;
        }

        try {
            $customerGroup = $this->customerGroupRepository->getById($customerGroupId);
            $taxClassId = $customerGroup->getTaxClassId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
        return $this->getAvaTaxTaxCode($taxClassId);
    }

    /**
     * Populate correct tax class IDs on products that are loaded from an order item collection
     *
     * When a product is loaded from the context of an order item, a Magento bug/quirk causes the product to get loaded
     * at the global configuration scope, rather than the scope of the order/order item. See this Github isue for
     * more context: https://github.com/classyllama/ClassyLlama_AvaTax/issues/34
     *
     * @param $items \Magento\Sales\Api\Data\InvoiceItemInterface[]|\Magento\Sales\Api\Data\CreditmemoItemInterface[]
     * @param $storeId
     */
    public function populateCorrectTaxClasses($items, $storeId)
    {
        $productIds = [];
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $productIds[] = $item->getOrderItem()->getProductId();
        }

        // Loading products via a collection rather than a repository as it's not possible to load a repository with
        // a store view scope applied. See http://magento.stackexchange.com/q/91278/2142
        $products = $this->productFactory->create()->getCollection()
            ->addAttributeToSelect('tax_class_id')
            ->addStoreFilter($storeId)
            ->addFieldToFilter('entity_id', ['in' => $productIds]);

        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }

        foreach ($items as $item) {
            $productId = $item->getOrderItem()->getProductId();
            if (isset($productsById[$productId])) {
                $productWithCorrectTaxClassId = $productsById[$productId];
                if ($productWithCorrectTaxClassId->getTaxClassId()) {
                    $item->getOrderItem()->getProduct()->setTaxClassId($productWithCorrectTaxClassId->getTaxClassId());
                }
            }
        }
    }
}
