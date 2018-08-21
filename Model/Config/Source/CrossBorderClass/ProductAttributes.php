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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;

class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\Config                                           $eavConfig
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray($isMultiselect = true, $foregroundCountries = '')
    {
        $entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('attribute_code')
            ->addFieldToSelect('frontend_label')
            ->addFieldToFilter('backend_type', ['in' => ['int', 'varchar', 'decimal', 'integer']])
            ->addFieldToFilter('frontend_label', ['notnull' => true])
            ->setEntityTypeFilter($entityTypeId)
            ->setOrder('frontend_label', Collection::SORT_ORDER_ASC);

        $data = $collection->getData();

        return array_map(function ($attribute) {
            return ['value' => $attribute['attribute_code'], 'label' => $attribute['frontend_label']];
        },
            $data);
    }
}
