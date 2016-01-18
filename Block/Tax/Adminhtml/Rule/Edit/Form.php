<?php 

namespace ClassyLlama\AvaTax\Block\Tax\Adminhtml\Rule\Edit;

/**
 * Class Form
 */
class Form extends \Magento\Tax\Block\Adminhtml\Rule\Edit\Form
{
    /**
     * Add note to fields informing admin how to manage tax classes
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $return = parent::_prepareForm();
        $fieldset = $this->getForm()->getElement('base_fieldset')->getContainer();
        $fieldset->getElement('tax_customer_class')->setNote(
            __(
                'Go to <a href="%1">Customer Tax Classes</a> to add AvaTax Customer Usage Types to tax classes.',
                $this->_urlBuilder->getUrl('avatax/tax_classes_customer')
            )
        );
        $fieldset->getElement('tax_product_class')->setNote(
            __(
                'Go to <a href="%1">Product Tax Classes</a> to add AvaTax Tax Codes to tax classes.',
                $this->_urlBuilder->getUrl('avatax/tax_classes_product')
            )
        );
        return $return;
    }
}
