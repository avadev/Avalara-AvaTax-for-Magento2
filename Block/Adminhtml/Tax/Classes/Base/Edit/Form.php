<?php
namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base\Edit;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer\NewClass;

/**
 * Using a trait since the users of this trait will need to extend from their respective tax class types
 */
trait Form
{
    /**
     * Modify structure of new status form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $form->getElement('base_fieldset')->removeField('is_new');
        $form->setAction(
            $this->getUrl('avatax/tax_classes_' .  \strtolower($this->classType) . '/save', ['id' => $this->getRequest()->getParam('id')])
        );
        return $this;
    }
}
