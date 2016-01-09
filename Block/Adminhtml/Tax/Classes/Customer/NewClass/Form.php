<?php
namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer\NewClass;

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
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;

    /**
     * {@inheritDoc}
     */
    public function addAvaTaxCodeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $fieldset->addField(
            'avatax_code',
            'select',
            [
                'name' => 'avatax_code',
                'label' => __('AvaTax Customer Usage Type'),
                'note' => 'Optional. The AvaTax <strong>Customer Usage Type</strong> (or <strong>Entity Use Code</strong>). Refer to the AvaTax documentation for more information.',
                'values' => $this->avaTaxCustomerUsageType->toOptionArray()
            ]
        );
    }
}
