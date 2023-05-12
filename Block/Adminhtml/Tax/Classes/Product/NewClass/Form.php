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

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Product\NewClass;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Create form
 */
/**
 * @codeCoverageIgnore
 */
class Form extends Base\NewClass\Form
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT;

    /**
     * {@inheritDoc}
     */
    public function addAvaTaxCodeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $fieldset->addField(
            'avatax_code',
            'text',
            [
                'name' => 'avatax_code',
                'label' => __('AvaTax Tax Code'),
                'note' => __('Optional. AvaTax system Tax Code or custom Tax Code. See <a href="%1" target="_blank">AvaTax documentation</a> for more details.', \ClassyLlama\AvaTax\Helper\Config::AVATAX_DOCUMENTATION_TAX_CODE_LINK),
                'class' => 'validate-length maximum-length-25',
            ]
        );
    }
}
