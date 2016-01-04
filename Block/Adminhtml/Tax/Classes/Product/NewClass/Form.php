<?php
namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Product\NewClass;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Create form
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
                'note' => __('Optional. AvaTax system Tax Code or custom Tax Code. See <a href="%1" href="_blank">AvaTax documentation</a> for more details.', \ClassyLlama\AvaTax\Model\Config::AVATAX_DOCUMENTATION_TAX_CODE_LINK),
            ]
        );
    }
}
