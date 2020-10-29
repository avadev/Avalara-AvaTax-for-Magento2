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

use ClassyLlama\AvaTax\Api\CrossBorderTypeRepositoryInterface;

class CrossBorderType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CrossBorderTypeRepositoryInterface
     */
    protected $crossBorderTypeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterfaceFactory
     */
    protected $criteriaInterfaceFactory;

    /**
     * @param CrossBorderTypeRepositoryInterface                    $crossBorderTypeRepository
     * @param \Magento\Framework\Api\SearchCriteriaInterfaceFactory $criteriaInterfaceFactory
     */
    public function __construct(
        CrossBorderTypeRepositoryInterface $crossBorderTypeRepository,
        \Magento\Framework\Api\SearchCriteriaInterfaceFactory $criteriaInterfaceFactory
    )
    {
        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = true)
    {
        $crossBorderTypes = $this->crossBorderTypeRepository->getList($this->criteriaInterfaceFactory->create())->getItems();

        $types = array_map(function($type) {
            return ['value' => $type->getEntityId(), 'label' => $type->getType()];
        }, $crossBorderTypes);

        array_unshift($types, ['value' => '', 'label' => __('Select Border Type')]);

        return $types;
    }
}