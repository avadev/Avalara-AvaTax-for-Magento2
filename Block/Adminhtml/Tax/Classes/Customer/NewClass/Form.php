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
                'note' => __('Optional. The AvaTax <strong>Customer Usage Type</strong> (or <strong>Entity Use Code</strong>). Refer to the <a href="%1" target="_blank">AvaTax documentation</a> for more information.', 'https://help.avalara.com/kb/001/What_are_the_exemption_reasons_for_each_Entity_Use_Code_used_for_Avalara_AvaTax%3F'),
                'values' => $this->avaTaxCustomerUsageType->toOptionArray()
            ]
        );
    }
}
