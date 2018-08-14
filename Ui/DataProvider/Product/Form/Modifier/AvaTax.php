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

namespace ClassyLlama\AvaTax\Ui\DataProvider\Product\Form\Modifier;

use ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\CrossBorderTypeFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class AvaTax extends AbstractModifier
{
    /**
     * @var CrossBorderTypeFactory
     */
    protected $crossBorderTypeFactory;

    /**
     * @param CrossBorderTypeFactory $crossBorderTypeFactory
     */
    public function __construct(CrossBorderTypeFactory $crossBorderTypeFactory)
    {
        $this->crossBorderTypeFactory = $crossBorderTypeFactory;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $meta['avatax']['children']['container_avatax_cross_border_type']['children']['avatax_cross_border_type']['arguments']['data'] = [
            'options' => $this->crossBorderTypeFactory->create(),
            'config' => array_merge(
                $meta['avatax']['children']['container_avatax_cross_border_type']['children']['avatax_cross_border_type']['arguments']['data']['config'],
                [
                    'label' => __('Cross Border Type'),
                    'dataType' => 'number',
                    'sortOrder' => 20,
                    'disableLabel' => true,
                    'filterOptions' => true,
                    'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                    'formElement' => 'select',
                    'componentType' => 'field',
                    'visible' => 1,
                    'required' => 1,
                    'multiple' => false,
                    'component' => 'Magento_Catalog/js/components/attribute-set-select',
                    'notice' => __(
                        'Your Cross Border Types will be assigned to specific products. A product’s Cross Border Type, combined with the destination country of a given transaction, will determine the appropriate Cross Border Class that applies (including its HS code and unit information).'
                    ),
                    'validation' => ['required-entry' => true]
                ]
            )
        ];

        return $meta;
    }
}
